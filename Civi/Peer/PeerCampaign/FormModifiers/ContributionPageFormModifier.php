<?php

/**
 * This is a helper class to break out code that needs to be called from hooks
 * within this extension.
 */

namespace Civi\Peer\PeerCampaign\FormModifiers;

use \CRM_Peer_ExtensionUtil as E;

class ContributionPageFormModifier extends AbstractFormModifier {

  /**
   * @inheritdoc
   */
  protected static function getTargetEntityTable() {
    return \CRM_Peer_BAO_PeerCampaign::OPTION_CONTRIBUTION_PAGE;
  }

  /**
   * @inheritdoc
   */
  protected static function getLabelForEnabling() {
    return E::ts('Enable Peer-To-Peer Pages for this contribution page?');
  }

  /**
   * @inheritdoc
   */
  protected static function insertMarkupIntoPage() {
  }

  /**
   *
   * @inheritdoc
   */
  static function getTargetEntityId($form = NULL) {

    // TODO - put some logic here

    // If we still haven't found the ID, then we're in trouble.
    throw new \CRM_Core_Exception('Unable to determine the ID of the target entity while trying to update a peer campaign within its associated target entity.');
  }

  /**
   * Entry point from hook_civicrm_tabset
   *
   * @param array $tabs
   * @param array $context
   */
  public static function tabset(&$tabs, $context) {
    if (!empty($context['contribution_page_id'])) {
      self::modifyTabsOfTargetEntityEdit($tabs);
    }
    else {
      self::modifyTabsOfTargetEntityManage($tabs, $context);
    }
  }

  /**
   * @param array $tabs
   */
  protected static function modifyTabsOfTargetEntityEdit(&$tabs) {
    // TODO - this displays the tab, but if I copy the link to the tab and make
    //        new request to that URL, then I don't get to the tab right away
    $tabs['peerCampaign'] = array(
      'title' => E::ts('Peer Pages'),
      'link' => NULL,
      'valid' => TRUE,
      'active' => TRUE,
      'current' => FALSE,
    );
  }

  /**
   * Modify the available options when clicking "Configure"
   *
   * @param array $tabs
   * @param array $context
   */
  protected static function modifyTabsOfTargetEntityManage(&$tabs, $context) {
    // This array element gets inserted into the array here
    // \CRM_Contribute_Page_ContributionPage::configureActionLinks
    //
    // Why REOPEN??? Umm, literally I just tried several CRM_Core_Action values
    // and this one worked (where others didn't). For example, RENEW does NOT
    // seem to work, even though no other elements in the core array use RENEW.
    //
    // Asked about it here
    // https://chat.civicrm.org/civicrm/pl/1dazc3eoi3bhfbjupwnys7xu4a
    //
    // More notes:
    // - Seems like it's better to use hook_civicrm_links here instead
    //
    // TODO
    $tabs[\CRM_Core_Action::REOPEN] = [
      'name' => E::ts('Peer Pages'),
      'title' => E::ts('Peer Pages'),
      'url' => $context['urlString']. 'peerCampaign',
      'qs' => $context['urlParams'],
      'uniqueName' => 'peerCampaign',
    ];
  }

}
