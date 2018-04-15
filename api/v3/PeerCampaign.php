<?php

use CRM_Peer_ExtensionUtil as E;
use CRM_Core_DAO_AllCoreTables as Tables;
use CRM_Peer_BAO_PeerCampaign as PeerCampaign;

/**
 * ============================== create ==============================
 *
 * This API is pretty much the basic stuff.
 */

/**
 * PeerCampaign.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_peer_campaign_create_spec(&$spec) {
  $spec['target_entity_table']['api.required'] = 1;
  $spec['target_entity_id']['api.required'] = 1;
}

/**
 * PeerCampaign.create API
 *
 * We add some additional validation here
 *
 * @param array $params
 *
 * @return array API result descriptor
 * @throws CiviCRM_API3_Exception|API_Exception
 */
function civicrm_api3_peer_campaign_create($params) {
  // Check to make sure target entity exists
  $entityName = Tables::getBriefName(Tables::getClassForTable($params['target_entity_table']));
  $targetEntity = civicrm_api3($entityName, 'get', [
    'return' => ['id'],
    'id' => $params['target_entity_id']
  ]);
  if ($targetEntity['count'] == 0) {
    throw new \API_Exception('Unable to locate target entity', 'missing_target_entity');
  }
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * ============================== delete ==============================
 *
 * Stick with the default here
 */

/**
 * PeerCampaign.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_peer_campaign_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * ============================== get ==============================
 *
 * This API is customized (in a way that's maybe a bit weird and hacky) so that
 * we can add some extra fields which are dynamically calculated.
 *
 * ## Why
 *
 * A PeerCampaign has a dynamic foreign key to its target entity, which can be
 * a ContributionPage, Event, or Survey. The PeerCampaign by itself has no title
 * but all of the possible target entity types have a title field. We'd like to
 * use the title of the target entity for situations where we need to display a
 * title for the PeerCampaign, but we can't use API joining here since it's a
 * dynamic foreign key. The same thing applies to other fields like is_active
 * and start_date.
 *
 * ## How
 *
 * Before running the 'get' query, we create a temporary table that has all the
 * rows and columns of civicrm_peer_campaign, plus some additional columns for
 * the dynamically calculated fields. So, yes, the fields are calculated via
 * SQL. We make an entire table because we actually want to *search* on some of
 * those fields (e.g. target_entity_title when selecting a PeerCampaign within
 * an entityRef field). Performance isn't a concern because it's unlikely that
 * the total number of PeerCampaigns will exceed "hundreds".
 *
 * After generating the temporary table, we trick the API select logic into
 * selecting from that temporary table instead of from civicrm_peer_campaign.
 *
 */

/**
 * Field definition for 'get' API
 *
 * We keep the default fields that already come with the 'get' API, and
 * then we adding 4 new dynamically calculated fields
 *
 * @see \CRM_Peer_BAO_PeerCampaign::setUpFullTmpTable
 *   for the SQL definition of these fields and their dynamically computed
 *   values.
 *
 * @param array $spec
 *
 * @throws \CiviCRM_API3_Exception
 */
function _civicrm_api3_peer_campaign_get_spec(&$spec) {
  $spec['target_entity_title'] = [
    'title' => E::ts('Target Entity Title'),
    'description' => E::ts('The title, as set by the target entity'),
    'type' => CRM_Utils_Type::T_STRING,
  ];
  $spec['target_entity_start_date'] = [
    'title' => E::ts('Target Entity Start Date'),
    'description' => E::ts('The date that the target entity starts'),
    'type' => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
  ];
  $spec['target_entity_is_active'] = [
    'title' => E::ts('Target Entity is Active'),
    'description' => E::ts('True if the target entity is active'),
    'type' => CRM_Utils_Type::T_BOOLEAN,
  ];
  $spec['is_entirely_active'] = [
    'title' => E::ts('Is Entirely Active'),
    'description' => E::ts('True only when this peer campaign AND its target entity are active'),
    'type' => CRM_Utils_Type::T_BOOLEAN,
  ];
}

/**
 * PeerCampaign.get API
 *
 * This is the part that's a bit hacky, since it's code that's copied from core
 * and adjusted a bit to work for this purpose.
 *
 * @see _civicrm_api3_basic_get
 *   for the original code that I copied when writing this function.
 *
 * @param array $params
 *
 * @return array API result descriptor
 * @throws \API_Exception
 * @throws \Exception
 */
function civicrm_api3_peer_campaign_get($params) {

  $entity = 'PeerCampaign';
  $options = _civicrm_api3_get_options_from_params($params);

  $query = new \Civi\API\MoreFlexibleApi3SelectQuery(
    $entity,
    CRM_Utils_Array::value('check_permissions', $params, FALSE)
  );
  PeerCampaign::setUpFullTmpTable();
  $query->setQueryFrom(PeerCampaign::fullTmpTableName());

  $query->where = $params;
  if ($options['is_count']) {
    $query->select = array('count_rows');
  }
  else {
    $query->select = array_keys(array_filter($options['return']));
    $query->orderBy = $options['sort'];
    $query->isFillUniqueFields = FALSE;
  }
  $query->limit = $options['limit'];
  $query->offset = $options['offset'];
  $result = $query->run();

  return civicrm_api3_create_success($result, $params, $entity, 'get');
}

/**
 * ============================== getlist ==============================
 *
 * This API is here so that we can make a nice entity ref field that references
 * PeerCampaign entities and displays the dynamically computed title of the
 * PeerCampaign (from the 'get' action) when selecting.
 */

/**
 * For some reason we need to have this function here even though it's empty.
 * Without this function, the getlist_output function doesn't get the full
 * $result variable (some fields are missing).
 *
 * @see _civicrm_api3_generic_getlist_params
 *
 * @param array $request
 */
function _civicrm_api3_peer_campaign_getlist_params(&$request) {

}

/**
 * Get peer campaign list output.
 *
 * We customize the output to provide the label and description needed by
 * the entityRef field.
 *
 * @see _civicrm_api3_generic_getlist_output
 *
 * @param array $result
 * @param array $request
 *
 * @return array
 * @throws \CiviCRM_API3_Exception
 */
function _civicrm_api3_peer_campaign_getlist_output($result, $request) {
  $output = [];
  if (empty($result['values'])) {
    return $output;
  }
  foreach ($result['values'] as $row) {
    $description = PeerCampaign::generateEntityRefDescription($row);
    $output[] = [
      'id' => $row['id'],
      'label' => $row['target_entity_title'],
      'description' => $description,
    ];
  }
  return $output;
}
