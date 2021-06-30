<?php
use CRM_HierarchicalACL_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_HierarchicalACL_Upgrader extends CRM_HierarchicalACL_Upgrader_Base {

  /**
   * Upgrade to 1.0.2:
   *  - Alter table `civicrm_hierarchicalacl_tree_cache`
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_1002() {
    $this->executeSqlFile('sql/upgrade_1002.sql');
    return TRUE;
  }

  public function install() {
    $this->executeSqlFile('sql/install.sql');
  }

  public function uninstall() {
    $this->executeSqlFile('sql/uninstall.sql');
  }

}
