<?php

/**
 * @group headless
 */
class api_v3_PeerCampaignTest extends \api_v3_PeerTest {

  /**
   * This should fail because a PeerCampaign cannot be associated with a Contact
   */
  public function testCreateFailsWithIncorrectEntityType() {
    $this->callAPIFailure('PeerCampaign', 'create', [
      'target_entity_table' => 'civicrm_contact',
      'target_entity_id' => 1,
    ]);
  }

  /**
   * Basic create/get cycle
   */
  public function testCreateSucceeds() {
    $params = [
      'target_entity_table' => 'civicrm_contribution_page',
      'target_entity_id' => $this->contributionPage['id'],
    ];
    $this->callApiSuccess('PeerCampaign', 'create', $params);
    $get = $this->callApiSuccess('PeerCampaign', 'get', $params);
    $result = array_values($get['values'])[0];
    $this->assertEquals($params['target_entity_table'], $result['target_entity_table']);
    $this->assertEquals($params['target_entity_id'], $result['target_entity_id']);
  }

  /**
   * This should fail because the contribution page doesn't exist
   */
  public function testCreateFailsWithMissingTargetEntity() {
    $this->callAPIFailure('PeerCampaign', 'create', [
      'target_entity_table' => 'civicrm_contribution_page',
      // Hopefully there's no contribution page with this ID in the test DB!
      'target_entity_id' => 999999999,
    ]);
  }

}
