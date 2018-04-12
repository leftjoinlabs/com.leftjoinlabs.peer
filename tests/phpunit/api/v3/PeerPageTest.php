<?php

/**
 * @group headless
 */
class api_v3_PeerPageTest extends \api_v3_PeerTest {

  /**
   * @throws \CiviCRM_API3_Exception
   */
  public function setUp() {
    parent::setUp();

    // Create Peer Campaign
    $this->peerCampaign = civicrm_api3('PeerCampaign', 'create', [
      'target_entity_table' => 'civicrm_contribution_page',
      'target_entity_id' => $this->contributionPage['id'],
    ]);
  }

  /**
   * @throws \CiviCRM_API3_Exception
   */
  public function tearDown() {
    // Delete Peer Campaign
    civicrm_api3('PeerCampaign', 'delete', [
      'id' => $this->peerCampaign['id']
    ]);

    parent::tearDown();
  }

  /**
   * Basic create/get cycle
   */
  public function testCreateSucceeds() {
    $params = [
      'peer_campaign_id' => $this->peerCampaign['id'],
      'contact_id' => $this->contact['id'],
      'title' => "My Page"
    ];
    $this->callApiSuccess('PeerPage', 'create', $params);
    $get = $this->callApiSuccess('PeerPage', 'get', $params);
    $result = array_values($get['values'])[0];

    $this->assertEquals($params['peer_campaign_id'], $result['peer_campaign_id']);
    $this->assertEquals($params['contact_id'], $result['contact_id']);
    $this->assertEquals($params['title'], $result['title']);
  }

}
