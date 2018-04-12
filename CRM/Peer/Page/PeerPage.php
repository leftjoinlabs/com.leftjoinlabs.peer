<?php

use CRM_Peer_ExtensionUtil as E;

/**
 * This is the "view" mode for PeerPages - the way that the general public
 * interacts with peer pages.
 *
 * Class CRM_Peer_Page_PeerPage
 */
class CRM_Peer_Page_PeerPage extends CRM_Core_Page {

  /**
   * @return null|void
   * @throws \CRM_Core_Exception
   * @throws \CiviCRM_API3_Exception
   */
  public function run() {
    $id = CRM_Utils_Request::retrieve('id', 'Positive', $this, TRUE);
    $peerPage = civicrm_api3('PeerPage', 'getsingle', ['id' => $id]);
    CRM_Utils_System::setTitle($peerPage['title']);
    $this->assign('peerPage', $peerPage);
    parent::run();
  }

}
