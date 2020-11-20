<?php
use CRM_HierarchicalACL_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_HierarchicalACL_Upgrader extends CRM_HierarchicalACL_Upgrader_Base {

  public function install() {
    $this->executeSqlFile('sql/install.sql');
  }

  public function uninstall() {
    $this->executeSqlFile('sql/uninstall.sql');
  }

}
