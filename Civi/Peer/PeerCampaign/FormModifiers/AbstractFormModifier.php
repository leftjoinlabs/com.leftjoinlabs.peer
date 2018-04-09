<?php

namespace Civi\Peer\PeerCampaign\FormModifiers;

use \CRM_Peer_ExtensionUtil as E;

abstract class AbstractFormModifier {

  /**
   * Add this prefix to all the form elements that we're injecting into this
   * form. This helps avoid name collisions with the standard form elements
   * (e.g `is_active`).

   * IMPORTANT: This prefix is hard-coded into these templates:
   *   templates/CRM/Peer/PeerCampaign/*
   */
  const PREFIX = 'peer_campaign_';

  /**
   * Entry point from hook_civicrm_buildForm.
   *
   * @param \CRM_Core_Form $form
   *
   * @throws \HTML_QuickForm_Error
   * @throws \CiviCRM_API3_Exception
   */
  public static function buildForm(&$form) {
    $acceptableActions = [
      \CRM_Core_Action::ADD,
      \CRM_Core_Action::UPDATE,
      NULL
    ];
    $actionIsAcceptable = in_array($form->_action, $acceptableActions);
    if ($actionIsAcceptable) {
      static::addFormElements($form);
      static::setDefaults($form);
      static::insertMarkupIntoPage();
    }
  }

  /**
   * Inject more elements into the form so that we can use the form to create
   * a Peer Campaign.
   *
   * @param \CRM_Core_Form $form
   *
   * @throws \HTML_QuickForm_Error
   */
  protected static function addFormElements(&$form) {
    // Checkbox to enable Peer Pages
    $form->addElement(
      'checkbox',
      self::PREFIX . 'is_active',
      static::getLabelForEnabling(),
      NULL,
      array('onclick' => "return showHideByValue('" . self::PREFIX . "is_active',true,'peer-campaign-fields','table-row','radio',false);")
    );

    // Select element to choose a profile
    $validProfiles = self::getValidProfiles();
    $form->add(
      'select',
      self::PREFIX . 'supporter_profile_id',
      ts('Supporter Profile'),
     ['' => ts('- select -')] + $validProfiles,
      FALSE
    );
  }

  /**
   * Retrieve the list of profiles needed for the form element where the
   * user chooses a profile that supporters will fill out when creating a Peer
   * Page. The profile is valid when all of the following conditions are met:
   * - The profile is enabled
   * - The profile is set to require CMS user registration
   * - The profile contains an email address
   * - The email address is required
   * - The email address is active
   *
   * Code originally copied from \CRM_PCP_BAO_PCP::buildPCPForm then re-written
   *
   * @return array
   *   [
   *     int $profileId => string $profileTitle,
   *     ...
   *   ]
   */
  protected static function getValidProfiles() {
    $query = "
      select
        prof.id,
        prof.title
      from civicrm_uf_group as prof
      join civicrm_uf_field as field on
        field.uf_group_id = prof.id
      where
        prof.is_active = 1 and
        prof.is_cms_user = 2 and
        field.field_name = 'email' and
        field.is_required = 1 and
        field.is_active = 1
      group by
        prof.id;
    ";
    $results = \CRM_Core_DAO::executeQuery($query)->fetchAll();
    $validProfiles = [];
    foreach ($results as $result) {
      $validProfiles[$result['id']] = $result['title'];
    }
    return $validProfiles;
  }

  /**
   * Validate that a chosen profile will work for setting up a Peer Page.
   *
   * @see \Civi\Peer\PeerCampaign\FormModifiers\AbstractFormModifier::getValidProfiles
   *   for logic behind which profiles are valid
   *
   * @param int $profileId
   *
   * @return bool
   *   TRUE if profile is valid
   */
  protected static function profileIsValid($profileId) {
    return array_key_exists($profileId, self::getValidProfiles());
  }

  /**
   * Update a peer_campaign entity after a its target entity is updated.
   *
   * Much of this code is mostly copied from
   * \CRM_PCP_Form_Contribute::postProcess
   *
   * @param \CRM_Core_Form $form
   *
   * @throws \CRM_Core_Exception
   * @throws \CiviCRM_API3_Exception
   */
  public static function postProcess(&$form) {
    $targetEntityId = static::getTargetEntityId($form);
    $peerCampaignId = static::getPeerCampaignIdByTargetEntityId($targetEntityId);

    if ($form->_action == \CRM_Core_Action::DELETE) {
      civicrm_api3('PeerCampaign', 'delete', ['id' => $peerCampaignId]);
      return;
    }

    $apiParams = self::getInjectedFormValues($form);
    $apiParams['id'] = $peerCampaignId;
    $apiParams['target_entity_table'] = static::getTargetEntityTable();
    $apiParams['target_entity_id'] = $targetEntityId;

    civicrm_api3('PeerCampaign', 'create', $apiParams);
  }

  /**
   * Inspect the form to find the submitted values that we care about here.
   * Only return values related to Peer Campaign. Ignore values related to the
   * form in its core state.
   *
   * @param \CRM_Core_Form $form
   *
   * @return array;
   */
  protected static function getInjectedFormValues($form) {
    $fullFormParams = $form->controller->exportValues($form->getVar('_name'));
    $injectedFormParams = self::unPrefixKeys($fullFormParams);

    // For some reason, the is_active checkbox doesn't come through as a form
    // parameter when it's left un-checked, so we need to add it back in like so
    if (empty($injectedFormParams['is_active'])) {
      $injectedFormParams['is_active'] = FALSE;
    }

    return $injectedFormParams;
  }

  /**
   * Add additional default values to the form so that it loads with data in the
   * peer_campaign fields if a corresponding peer_campaign already exists.
   *
   * @param \CRM_Core_Form $form
   *
   * @throws \HTML_QuickForm_Error
   * @throws \CiviCRM_API3_Exception
   */
  protected static function setDefaults(&$form) {
    $fieldValuesForBlankForm = [
      'is_active' => FALSE,
    ];
    $targetEntityId = self::tryGetTargetEntityId($form);
    $fieldValuesForEditForm = static::getPeerCampaignFieldsByTargetEntityId($targetEntityId);
    $defaults = !empty($fieldValuesForEditForm) ? $fieldValuesForEditForm : $fieldValuesForBlankForm;
    $prefixedDefaults = self::prefixKeys($defaults);
    $form->setDefaults($prefixedDefaults);
  }

  /**
   * Entry point from hook_civicrm_validateForm.
   *
   * @param array $fields
   * @param \CRM_Core_Form $form
   * @param array $errors
   */
  public static function validate(&$fields, &$form, &$errors) {
    if (empty($fields[self::PREFIX . 'is_active']) || $fields[self::PREFIX . 'is_active'] != 1) {
      return;
    }

    // Require a profile to be chosen, and make sure the profile has an email address
    if (empty($fields[self::PREFIX . 'supporter_profile_id'])) {
      $errors[self::PREFIX . 'supporter_profile_id'] = E::ts('Supporter profile is a required field.');
    }
    else {
      if (!self::profileIsValid($fields[self::PREFIX . 'supporter_profile_id'])) {
        $errors[self::PREFIX . 'supporter_profile_id'] = E::ts('The chosen profile is not valid.');
      }
    }
    $a = 0;
  }

  /**
   * Look for a PeerCampaign entity, given info about its target entity.
   *
   * @param int $targetEntityId
   *
   * @return array
   *
   * @throws \CiviCRM_API3_Exception
   */
  static function getPeerCampaignFieldsByTargetEntityId($targetEntityId) {
    if (empty($targetEntityId)) {
      return [];
    }
    $api = civicrm_api3('PeerCampaign', 'get', array(
      'target_entity_table' => static::getTargetEntityTable(),
      'target_entity_id' => $targetEntityId,
    ));
    $result = empty($api['values']) ? [] : array_values($api['values'])[0];
    return $result;
  }

  /**
   * Look for the ID of a PeerCampaign entity, given the ID of its target entity.
   *
   * @param int $targetEntityId
   *
   * @return int
   *
   * @throws \CiviCRM_API3_Exception
   */
  static function getPeerCampaignIdByTargetEntityId($targetEntityId) {
    if (empty($targetEntityId)) {
      return NULL;
    }
    $peerCampaignFields = static::getPeerCampaignFieldsByTargetEntityId($targetEntityId);
    if (empty($peerCampaignFields['id'])) {
      return NULL;
    }
    return (int) $peerCampaignFields['id'];
  }

  /**
   * Try to find the ID of the target entity, but don't complain if we can't
   * find it. For example, when creating a new target entity, we won't have its
   * ID yet, so we want to be able to proceed without failure in that case.
   *
   * @param \CRM_Core_Form $form
   *
   * @return int|null
   */
  static function tryGetTargetEntityId($form) {
    try {
      return static::getTargetEntityId($form);
    } catch (\Exception $e) {
      return NULL;
    }
  }

  /**
   * Return $params, but with the array keys prefixed by the special prefix
   * we use to avoid name collisions for form elements
   *
   * @param array $params
   *
   * @return array
   */
  protected static function prefixKeys($params) {
    $result = [];
    foreach ($params as $k => $v) {
      $result[self::PREFIX . $k] = $v;
    }
    return $result;
  }

  /**
   * Return $params, with elements removed that are not prefixed, and with the
   * prefixes removed from the elements that do have them
   *
   * @param array $params
   *
   * @return array
   */
  protected static function unPrefixKeys($params) {
    $result = [];
    foreach ($params as $key => $v) {
      $pattern = '/^' . self::PREFIX . '/';
      if (preg_match($pattern, $key)) {
        $newKey = preg_replace($pattern, '', $key);
        $result[$newKey] = $v;
      }
    }
    return $result;
  }

  /**
   * Produce a string that we can use to label the form checkbox to control
   * whether this Peer Campaign is enabled or disabled.
   *
   * @return string
   *  e.g. "Enable Peer-To-Peer Pages for this contribution page?"
   */
  protected abstract static function getLabelForEnabling();

  /**
   * @return string
   *   e.g. "civicrm_contribution_page"
   */
  protected abstract static function getTargetEntityTable();

  /**
   * Get the ID of the entity that we're editing with this form.
   *
   * @param \CRM_Campaign_Form_Petition $form
   *
   * @return int
   *
   * @throws \CRM_Core_Exception
   *   if the target entity can't be found
   */
  protected abstract static function getTargetEntityId($form = NULL);

  /**
   * Alter the page to add the markup for our new form elements
   *
   * @return void
   */
  protected abstract static function insertMarkupIntoPage();

}
