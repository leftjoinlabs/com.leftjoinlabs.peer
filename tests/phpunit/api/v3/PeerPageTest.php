<?php

/**
 * @group headless
 */
class api_v3_PeerPageTest extends \api_v3_PeerTest {

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

    // Create Peer Campaign
    $peerCampaign = $this->callApiSuccess('PeerCampaign', 'create', [
      'target_entity_table' => 'civicrm_contribution_page',
      'target_entity_id' => $contributionPage['id'],
    ]);

    // Create contact
    $contact = $this->callApiSuccess('Contact', 'create', [
      'contact_type' => 'Individual',
      'first_name' => 'Foo',
      'last_name' => 'Bar',
    ]);

    $params = [
      'peer_campaign_id' => $peerCampaign['id'],
      'contact_id' => $contact['id'],
      'title' => "My Page"
    ];
    $this->callApiSuccess('PeerPage', 'create', $params);
    $get = $this->callApiSuccess('PeerPage', 'get', $params);
    $result = array_values($get['values'])[0];

    $this->assertEquals($params['peer_campaign_id'], $result['peer_campaign_id']);
    $this->assertEquals($params['contact_id'], $result['contact_id']);
    $this->assertEquals($params['title'], $result['title']);

    // Delete Peer Campaign
    $this->callApiSuccess('PeerCampaign', 'delete', [
      'id' => $peerCampaign['id']
    ]);

    // Delete contact
    $this->callApiSuccess('Contact', 'delete', [
      'id' => $contact['id']
    ]);
  }

}
