<?php

use CRM_Peer_ExtensionUtil as E;
use CRM_Core_DAO_AllCoreTables as CoreTables;

class CRM_Peer_BAO_PeerCampaign extends CRM_Peer_DAO_PeerCampaign {

  /**
   * Option for entity_table field
   */
  const OPTION_CONTRIBUTION_PAGE = 'civicrm_contribution_page';

  /**
   * Option for entity_table field
   */
  const OPTION_EVENT = 'civicrm_event';

  /**
   * Option for entity_table field
   */
  const OPTION_SURVEY = 'civicrm_survey';

  /**
   * Whitelist of possible values for the entity_table field. Used as a
   * pseudoconstant callback in PeerCampaign.xml
   *
   * @return array
   *   [string $name => string $label].
   */
  public static function entityTableOptions() {
    return [
      self::OPTION_CONTRIBUTION_PAGE => 'ContributionPage',
      self::OPTION_EVENT => 'Event',
      self::OPTION_SURVEY => 'Survey',
    ];
  }

  public static function fullTmpTableName() {
    // TODO make this more robust by checking to see if this table name already
    // exists
    return 'tmp_peer_campaign';
  }

  /**
   * Set up a MySQL temporary table that has all the Peer Campaign data
   * as well as columns for some of the fields of the target entity. After
   * this table is set up, we can SELECT from it easily.

   * @see _civicrm_api3_peer_campaign_get_spec
   *   For the other place these fields are defined explicitly
   */
  public static function setUpFullTmpTable() {
    $tableName = self::fullTmpTableName();
    $sql = "
      drop temporary table if exists $tableName;
      create temporary table $tableName like civicrm_peer_campaign;
      alter table $tableName add (
        target_entity_title      varchar(255),
        target_entity_start_date datetime,
        target_entity_is_active  tinyint(4) not null,
        is_entirely_active       tinyint(4) not null
      );
      alter table $tableName add index target_entity_title      (target_entity_title);
      alter table $tableName add index target_entity_start_date (target_entity_start_date);
      alter table $tableName add index target_entity_is_active  (target_entity_is_active);
      alter table $tableName add index is_entirely_active       (is_entirely_active);
      insert into $tableName
      select
        pc.*,
        coalesce(cp.title,      ev.title,      su.title),
        coalesce(cp.start_date, ev.start_date, su.created_date),
        coalesce(cp.is_active,  ev.is_active,  su.is_active, 0),
        coalesce(cp.is_active,  ev.is_active,  su.is_active, 0) & pc.is_active
      from civicrm_peer_campaign pc
      left join civicrm_contribution_page cp on
        pc.target_entity_table = 'civicrm_contribution_page' and
        pc.target_entity_id = cp.id
      left join civicrm_event ev on
        pc.target_entity_table = 'civicrm_event' and
        pc.target_entity_id = ev.id
      left join civicrm_survey su on
        pc.target_entity_table = 'civicrm_survey' and
        pc.target_entity_id = su.id
    ";
    $queries = preg_split('/;/', $sql);
    foreach ($queries as $query) {
      // Safe because there are no user inputs here
      CRM_Core_DAO::executeQuery($query);
    }
  }

  /**
   * Make a description for the choices displayed in the EntityRef field

   * @see _civicrm_api3_peer_campaign_getlist_output
   *
   * @param array $fields
   *   array of fields of a PeerCampaign
   *
   * @return string[]
   *   Each element in this array is one line in the description
   */
  public static function generateEntityRefDescription($fields) {
    $targetEntityName = CoreTables::getBriefName(
      CoreTables::getClassForTable($fields['target_entity_table'])
    );
    $id = $fields['id'];
    $description = [];
    $description[] = "($targetEntityName ID:$id)";
    if (!$fields['is_entirely_active']) {
      $description[] = E::ts('Not Active');
    }
    return $description;
  }

}
