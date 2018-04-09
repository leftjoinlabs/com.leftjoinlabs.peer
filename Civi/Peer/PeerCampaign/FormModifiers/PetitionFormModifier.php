<?php

/**
 * This is a helper class to break out code that needs to be called from hooks
 * within this extension.
 */

namespace Civi\Peer\PeerCampaign\FormModifiers;

use \CRM_Peer_ExtensionUtil as E;

class PetitionFormModifier extends AbstractFormModifier {

  /**
   * Array key for the "targetEntityId" value that we need to store in $GLOBALS
   * The prefix helps avoid name collisions
   */
  const GLOBAL_KEY_TARGET_ENTITY_ID = 'peer_targetEntityId';

  /**
   * @inheritdoc
   */
  protected static function getTargetEntityTable() {
    return \CRM_Peer_BAO_PeerCampaign::OPTION_SURVEY;
  }

  /**
   * @inheritdoc
   */
  protected static function getLabelForEnabling() {
    return E::ts('Enable Peer-To-Peer Pages for this petition?');
  }

  /**
   * @inheritdoc
   */
  protected static function insertMarkupIntoPage() {
    \CRM_Core_Region::instance('page-body')->add(array(
      'template' => "CRM/Peer/PeerCampaign/Form/Petition.tpl",
    ));
  }

  /**
   * Get the ID of a survey after it has been updated so that we can update
   * the peer_campaign in hook_civicrm_postProcess.
   *
   * When the form is saved to create or update a Survey entity,
   * hook_civicrm_post fires before hook_civicrm_postProcess. We get the form
   * values in hook_civicrm_postProcess, but in the case of creating a new
   * survey, we don't get the survey ID in hook_civicrm_postProcess. So we grab
   * the ID here and store it globally to access it from
   * hook_civicrm_postProcess later on.
   *
   * @param int $surveyId
   */
  public static function post($surveyId) {
    self::setTargetEntityId($surveyId);
  }

  /**
   * @see \Civi\Peer\PeerCampaign\FormModifiers\PetitionFormModifier::getTargetEntityId
   *
   * @param int $targetEntityId
   */
  protected static function setTargetEntityId($targetEntityId) {
    $GLOBALS[self::GLOBAL_KEY_TARGET_ENTITY_ID] = $targetEntityId;
  }

  /**
   * Look in multiple places to find the ID of the survey we're updating, so
   * that we can use this ID update a peer_campaign. We need to do this weird
   * logic here because, depending on the survey action (create/update/delete),
   * the survey ID will be in different places.
   *
   * In general:
   * - We want to update the peer_campaign from within hook_civicrm_postProcess
   *   because that's where we get the form values for the peer_campaign fields.
   *
   * When creating a survey:
   * - We need to use the global var because the form doesn't supply the ID
   *   for the survey
   *
   * When deleting a survey:
   * - We need to use $form->_surveyId because hook_civicrm_postProcess runs
   *   before hook_civicrm_post (so the global var won't have been set yet).
   *
   * When updating a survey:
   * - ?? - TODO - update this comment
   *
   * Set the ID of the saved survey in this hacky global var. This is because
   * we can't reliably get the ID from within hook_civicrm_postProcess (where
   * we need it) because the form won't have an ID if it's a new survey. So
   * we grab the ID from hook_civicrm_post and save it globally.
   *
   * @inheritdoc
   */
  static function getTargetEntityId($form = NULL) {

    if (!empty($form->_surveyId)) {
      return (int) $form->_surveyId;
    }

    if (!empty($GLOBALS[self::GLOBAL_KEY_TARGET_ENTITY_ID])) {
      return (int) $GLOBALS[self::GLOBAL_KEY_TARGET_ENTITY_ID];
    }

    // If we still haven't found the ID, then we're in trouble.
    throw new \CRM_Core_Exception('Unable to determine the ID of the target entity while trying to update a peer campaign within its associated target entity.');
  }

}
