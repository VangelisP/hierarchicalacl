<?php

require_once 'hierarchicalacl.civix.php';
// phpcs:disable
use CRM_HierarchicalACL_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_aclWhereClause().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_aclWhereClause
 */
function hierarchicalacl_civicrm_aclWhereClause($type, &$tables, &$whereTables, &$contactID, &$where) {
  if (!$contactID) {
    return;
  }

  if (!CRM_Core_Permission::check('edit all contacts')) {
    $acls = CRM_HierarchicalACL_BAO_HierarchicalACL::getHierarchicalACLs();
    CRM_HierarchicalACL_BAO_HierarchicalACL::createTreeTable($contactID, $type, $acls);
    $tmpPermsTableName = CRM_HierarchicalACL_BAO_HierarchicalACL::createPermissionsTable($contactID);

    // Do not add in the permission table if there's no contacts to add
    $check = CRM_Core_DAO::singleValueQuery("SELECT count(contact_id) as contact_count FROM {$tmpPermsTableName}");
    if (!empty($check) || (empty($check) && empty($where))) {
      $tables['$tmpPermsTableName'] = $whereTables['$tmpPermsTableName'] =
        " LEFT JOIN $tmpPermsTableName permrelationships
       ON (contact_a.id = permrelationships.contact_id)";
      if (empty($where)) {
        $where = " permrelationships.contact_id IS NOT NULL ";
      }
      else {
        $where = '(' . $where . " OR permrelationships.contact_id IS NOT NULL " . ')';
      }
    }
  }
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
function hierarchicalacl_civicrm_navigationMenu(&$menu) {
  $path = "Administer/Users and Permissions";
  _hierarchicalacl_civix_insert_navigation_menu($menu, $path, array(
    'label' => E::ts('Hierarchical ACL Configuration'),
    'name' => 'HierarchicalACL_config',
    'url' => 'civicrm/admin/hierarchicalacl/config?reset=1',
    'permission' => 'administer CiviCRM',
    'operator' => '',
    'separator' => 0,
  ));
  _hierarchicalacl_civix_navigationMenu($menu);
}

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function hierarchicalacl_civicrm_config(&$config) {
  _hierarchicalacl_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function hierarchicalacl_civicrm_xmlMenu(&$files) {
  _hierarchicalacl_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function hierarchicalacl_civicrm_install() {
  _hierarchicalacl_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function hierarchicalacl_civicrm_postInstall() {
  _hierarchicalacl_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function hierarchicalacl_civicrm_uninstall() {
  _hierarchicalacl_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function hierarchicalacl_civicrm_enable() {
  _hierarchicalacl_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function hierarchicalacl_civicrm_disable() {
  _hierarchicalacl_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function hierarchicalacl_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _hierarchicalacl_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function hierarchicalacl_civicrm_managed(&$entities) {
  _hierarchicalacl_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function hierarchicalacl_civicrm_caseTypes(&$caseTypes) {
  _hierarchicalacl_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function hierarchicalacl_civicrm_angularModules(&$angularModules) {
  _hierarchicalacl_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function hierarchicalacl_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _hierarchicalacl_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function hierarchicalacl_civicrm_entityTypes(&$entityTypes) {
  _hierarchicalacl_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_thems().
 */
function hierarchicalacl_civicrm_themes(&$themes) {
  _hierarchicalacl_civix_civicrm_themes($themes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function hierarchicalacl_civicrm_preProcess($formName, &$form) {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
//function hierarchicalacl_civicrm_navigationMenu(&$menu) {
//  _hierarchicalacl_civix_insert_navigation_menu($menu, 'Mailings', array(
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ));
//  _hierarchicalacl_civix_navigationMenu($menu);
//}
