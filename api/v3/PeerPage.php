<?php
use CRM_Peer_ExtensionUtil as E;

/**
 * PeerPage.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_peer_page_create_spec(&$spec) {
  $spec['peer_campaign_id']['api.required'] = 1;
  $spec['contact_id']['api.required'] = 1;
}

/**
 * PeerPage.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_peer_page_create($params) {
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * PeerPage.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_peer_page_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * @param array $spec description of fields supported by this API call
 *
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_peer_page_get_spec(&$spec) {
  $spec['peer_campaign_id'] = [
    'title' => 'Peer Campaign',
    'FKClassName' => 'CRM_Peer_DAO_PeerCampaign',
    'FKApiName' => 'PeerCampaign',
    'supports_joins' => TRUE,
  ];
  $spec['contact_id'] = [
    'title' => 'Contact',
    'FKClassName' => 'CRM_Contact_DAO_Contact',
    'FKApiName' => 'Contact',
    'supports_joins' => TRUE,
  ];
}

/**
 * PeerPage.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_peer_page_get($params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}
