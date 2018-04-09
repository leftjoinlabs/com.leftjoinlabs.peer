<?php
use CRM_Peer_ExtensionUtil as E;
use CRM_Core_DAO_AllCoreTables as Tables;

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
 * PeerCampaign.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_peer_campaign_get($params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}
