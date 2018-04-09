<?php

require_once 'peer.civix.php';
use CRM_Peer_ExtensionUtil as E;
use Civi\Peer\PeerCampaign\FormModifiers\PetitionFormModifier;

/**
 * Implements hook_civicrm_buildForm().
 *
 * @param string $formName
 * @param mixed $form
 *
 * @throws \HTML_QuickForm_Error
 * @throws \CiviCRM_API3_Exception
 */
function peer_civicrm_buildForm($formName, &$form) {
  if (($formName == 'CRM_Campaign_Form_Petition')) {
    PetitionFormModifier::buildForm($form);
  }
}

/**
 * Implements hook_civicrm_postProcess().
 *
 * @param string $formName
 * @param CRM_Core_Form $form
 *
 * @throws \CRM_Core_Exception
 * @throws \CiviCRM_API3_Exception
 */
function peer_civicrm_postProcess($formName, &$form) {
  if (($formName == 'CRM_Campaign_Form_Petition')) {
    PetitionFormModifier::postProcess($form);
  }
}

/**
 * @param string $op
 * @param string $objectName
 * @param int $objectId
 * @param mixed $objectRef
 */
function peer_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  if ($objectName = 'Survey') {
    PetitionFormModifier::post($objectId);
  }
}

/**
 * Implements hook_civicrm_validateForm().
 *
 * @param string $formName
 * @param array $fields
 * @param array $files
 * @param CRM_Core_Form $form
 * @param array $errors
 */
function peer_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  if (($formName == 'CRM_Campaign_Form_Petition')) {
    PetitionFormModifier::validate($fields, $form, $errors);
  }
}

// ============================== civix stubs ==================================

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function peer_civicrm_config(&$config) {
  _peer_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function peer_civicrm_xmlMenu(&$files) {
  _peer_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function peer_civicrm_install() {
  _peer_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function peer_civicrm_postInstall() {
  _peer_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function peer_civicrm_uninstall() {
  _peer_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function peer_civicrm_enable() {
  _peer_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function peer_civicrm_disable() {
  _peer_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function peer_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _peer_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function peer_civicrm_managed(&$entities) {
  _peer_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function peer_civicrm_caseTypes(&$caseTypes) {
  _peer_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function peer_civicrm_angularModules(&$angularModules) {
  _peer_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function peer_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _peer_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function peer_civicrm_entityTypes(&$entityTypes) {
  _peer_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function peer_civicrm_navigationMenu(&$menu) {
  _peer_civix_insert_navigation_menu($menu, NULL, array(
    'label' => E::ts('The Page'),
    'name' => 'the_page',
    'url' => 'civicrm/the-page',
    'permission' => 'access CiviReport,access CiviContribute',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _peer_civix_navigationMenu($menu);
} // */
