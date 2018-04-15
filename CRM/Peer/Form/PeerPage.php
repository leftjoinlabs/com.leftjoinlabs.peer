<?php

use CRM_Peer_ExtensionUtil as E;
use CRM_Core_Action as Action;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Peer_Form_PeerPage extends CRM_Core_Form {

  /**
   * @var int
   *   GET param for ID of this PeerPage (when editing)
   */
  protected $id;

  /**
   * @var int
   *   GET param for ID of the contact to create the page for (when adding)
   */
  protected $cid;

  /**
   * @var int
   *   GET param for the ID of the peer campaign to associate this peer page
   *   with (when creating)
   */
  protected $campaign_id;

  /**
   * @var array|null
   *   Parameters for the PeerPage we're editing (if applicable)
   */
  protected $peerPage;

  /**
   * @throws \CiviCRM_API3_Exception
   */
  public function preProcess() {
    $this->receiveGetParams();
    $this->determineAction();
    $this->setPageTitle(E::ts('Peer Page'));
    $this->fetchEntities();
    $this->checkPermissions();
    parent::preProcess();
  }

  /**
   * @throws \CiviCRM_API3_Exception
   */
  protected function fetchEntities() {
    if ($this->_action == Action::UPDATE) {
      $this->peerPage = civicrm_api3('PeerPage', 'getsingle', ['id' => $this->id]);
      // TODO handle missing page gracefully
    }
  }

  /**
   * Handle
   */
  protected function checkPermissions() {
    // TODO
  }

  /**
   *
   */
  protected function receiveGetParams() {
    $this->id = CRM_Utils_Request::retrieve('id', 'Positive', $this, FALSE);
    $this->cid = CRM_Utils_Request::retrieve('cid', 'Positive', $this, FALSE);
    $this->campaign_id = CRM_Utils_Request::retrieve('campaign_id', 'Positive', $this, FALSE);
  }

  /**
   *
   */
  protected function determineAction() {
    $this->_action = empty($this->id) ? Action::ADD : Action::UPDATE;
  }

  public function buildQuickForm() {

    // Peer Campaign reference
    $this->addEntityRef('peer_campaign_id', E::ts('Peer Campaign'), [
      'entity' => 'PeerCampaign',
      'create' => FALSE,
      'select' => ['minimumInputLength' => 0],
      'api' => [
        'search_field' => 'target_entity_title',
        'label_field' => 'target_entity_title',
      ]
    ], TRUE);

    // Contact reference
    $this->addEntityRef('contact_id', E::ts('Contact'), [
      'create' => TRUE,
      'api' => ['extra' => ['email']],
    ], TRUE);

    // Page title
    $this->add('text', 'title', E::ts('Title'), NULL, TRUE);

    // Body
    $this->add('wysiwyg', 'body', E::ts('Page Text'));

    // Goal amount
    $this->add('text', 'goal_amount', E::ts('Goal'));

    // Photo upload
    // TODO

    $this->addButtons([
      [
        'type' => 'submit',
        'name' => ts('Save'),
        'isDefault' => TRUE,
      ],
      [
        'type' => 'cancel',
        'name' => ts('Cancel'),
      ],
    ]);

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  /**
   * @return array
   *   Default values for the form.
   */
  public function setDefaultValues() {
    if ($this->_action != Action::UPDATE) {
      return [];
    }
    return $this->peerPage;
  }

  public function postProcess() {
    $params = $this->controller->exportValues($this->_name);

    parent::postProcess();
  }


  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = [];
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
