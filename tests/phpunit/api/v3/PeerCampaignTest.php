<?php

use CRM_Peer_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;
use CRM_Peer_BAO_PeerCampaign as PeerCampaign;

/**
 * Test the PeerCampaign API functions
 *
 * Tips:
 *  - With HookInterface, you may implement CiviCRM hooks directly in the test class.
 *    Simply create corresponding functions (e.g. "hook_civicrm_post(...)" or similar).
 *  - With TransactionalInterface, any data changes made by setUp() or test****() functions will
 *    rollback automatically -- as long as you don't manipulate schema or truncate tables.
 *    If this test needs to manipulate schema or truncate tables, then either:
 *       a. Do all that using setupHeadless() and Civi\Test.
 *       b. Disable TransactionalInterface, and handle all setup/teardown yourself.
 *
 * @group headless
 */
class api_v3_PeerCampaignTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  use \Civi\Test\Api3TestTrait;

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp() {
    parent::setUp();
  }

  public function tearDown() {
    parent::tearDown();
  }

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
    $contributionPage = $this->callApiSuccess(
      'ContributionPage',
      'create',
      array(
        'financial_type_id' => 1,
        'title' => "Test contribution page",
        'is_active' => 1,
      )
    );
    $params = [
      'target_entity_table' => 'civicrm_contribution_page',
      'target_entity_id' => $contributionPage['id'],
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
      // Hopefully these's no contribution page with this ID in the test DB!
      'target_entity_id' => 999999999,
    ]);
  }

}
