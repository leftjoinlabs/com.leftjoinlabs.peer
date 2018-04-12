<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * Test the PeerPage API functions
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
class api_v3_PeerTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  use \Civi\Test\Api3TestTrait;

  /**
   * @var array
   *   params for an existing contribution page
   */
  protected $contributionPage;

  /**
   * @var array
   *   params for an existing peer campaign
   */
  protected $peerCampaign;

  /**
   * @var array
   *   params for an existing contact
   */
  protected $contact;

  /**
   * @return \Civi\Test\CiviEnvBuilder
   * @throws \CRM_Extension_Exception_ParseException
   */
  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  /**
   * @throws \CiviCRM_API3_Exception
   */
  public function setUp() {
    parent::setUp();

    // Create contact
    $this->contact = civicrm_api3('Contact', 'create', [
      'contact_type' => 'Individual',
      'first_name' => 'Foo',
      'last_name' => 'Bar',
    ]);

    // Create contribution page
    $this->contributionPage = civicrm_api3(
      'ContributionPage',
      'create',
      array(
        'financial_type_id' => 1,
        'title' => "Test contribution page",
        'is_active' => 1,
      )
    );
  }

  /**
   * @throws \CiviCRM_API3_Exception
   */
  public function tearDown() {
    parent::tearDown();

    // Delete contribution page
    civicrm_api3('ContributionPage', 'delete', [
      'id' => $this->contributionPage['id']
    ]);

    // Delete contact
    civicrm_api3('Contact', 'delete', [
      'id' => $this->contact['id']
    ]);
  }

}
