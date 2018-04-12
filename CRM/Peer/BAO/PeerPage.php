<?php
use CRM_Peer_ExtensionUtil as E;

class CRM_Peer_BAO_PeerPage extends CRM_Peer_DAO_PeerPage {

  /**
   * Create a new PeerPage based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Peer_DAO_PeerPage|NULL
   *
  public static function create($params) {
    $className = 'CRM_Peer_DAO_PeerPage';
    $entityName = 'PeerPage';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  } */

}
