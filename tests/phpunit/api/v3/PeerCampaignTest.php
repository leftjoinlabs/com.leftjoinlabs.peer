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
    // Create contribution page
    $contributionPage = $this->callApiSuccess('ContributionPage', 'create', [
      'financial_type_id' => 1,
      'title' => "Test contribution page",
      'is_active' => 1,
    ]);

    $params = [
      'target_entity_table' => 'civicrm_contribution_page',
      'target_entity_id' => $contributionPage['id'],
    ];
    $peerCampaign = $this->callApiSuccess('PeerCampaign', 'create', $params);
    $get = $this->callApiSuccess('PeerCampaign', 'get', $params);
    $result = array_values($get['values'])[0];
    $this->assertEquals($params['target_entity_table'], $result['target_entity_table']);
    $this->assertEquals($params['target_entity_id'], $result['target_entity_id']);

    // Clean up
    $this->callAPISuccess('PeerCampaign', 'delete', [
      'id' => $peerCampaign['id']
    ]);
    $this->callAPISuccess('ContributionPage', 'delete', [
      'id' => $contributionPage['id']
    ]);
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

  /**
   * Test that we can search on target_entity_title like we do when we use a
   * entityRef field
   */
  public function testGetlistSucceeds() {
    // A "funny" string that we'll use for searching
    $funnyString = "zxqjmkrpy";

    // Create contribution page with a funny title
    $contributionPageParams = [
      'financial_type_id' => 1,
      'title' => "Funny title with $funnyString in it",
      'is_active' => 1,
    ];
    $contributionPage = $this->callApiSuccess(
      'ContributionPage',
      'create',
      $contributionPageParams
    );

    // Create PeerCampaign associated with the contribution page
    $peerCampaignParams = [
      'target_entity_table' => 'civicrm_contribution_page',
      'target_entity_id' => $contributionPage['id'],
    ];
    $peerCampaign = $this->callApiSuccess(
      'PeerCampaign',
      'create',
      $peerCampaignParams
    );

    $result = $this->callApiSuccess('PeerCampaign', 'getlist', [
      'search_field' => 'target_entity_title',
      'label_field' => 'target_entity_title',
      'input' => "%$funnyString%",
    ]);

    // Make sure we get one and only one result (because there shouldn't be
    // any other PeerCampaigns with a target_entity_title like that!
    // TODO fix this test so that re-running it still works. Seems like entities
    // are left behind for some reason.
    $this->assertEquals(1, $result['count']);

    // Mae sure the one we got is the right one.
    $this->assertEquals($peerCampaign['id'], $result['values'][0]['id']);

    // Clean up
    $this->callAPISuccess('PeerCampaign', 'delete', [
      'id' => $peerCampaign['id']
    ]);
    $this->callAPISuccess('ContributionPage', 'delete', [
      'id' => $contributionPage['id']
    ]);
  }

}
