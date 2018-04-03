<?php
use CRM_Peer_ExtensionUtil as E;

class CRM_Peer_BAO_PeerCampaign extends CRM_Peer_DAO_PeerCampaign {

  /**
   * Option for total_function field
   */
  const OPTION_SUM = 'sum';

  /**
   * Option for total_function field
   */
  const OPTION_COUNT = 'count';

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
   * Possible values for the total_function field. Used as a pseudoconstant
   * callback in PeerCampaign.xml
   *
   * @return array
   *   [string $name => string $label].
   */
  public static function totalFunctionOptions() {
    return [
      self::OPTION_SUM => self::OPTION_SUM,
      self::OPTION_COUNT => self::OPTION_COUNT,
    ];
  }

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

}
