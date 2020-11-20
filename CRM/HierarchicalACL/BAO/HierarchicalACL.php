<?php


class CRM_HierarchicalACL_BAO_HierarchicalACL {

  static protected $treeTable = [];
  static protected $permsTables = [];
  static protected $config;

  /**
   * Returns HierarchicalACLs
   *
   * @return array
   */
  public static function getHierarchicalACLs() {
    if (!isset(self::$config)) {
      self::$config = Civi::settings()->get('hierarchicalacl_config');
    }

    return self::$config;
  }

  /**
   * Returns if the Hierarchicalacl Tree needs to be rebuilt
   *
   * @param int $contactID
   * @param int $acl_id
   * @param array $acl
   * @return bool
   */
  public static function needsTreeCacheRefresh($contactID, $acl_id, $acl) {
    if (empty($acl["cache_timeout"])) {
      return TRUE;
    }
    else {
      $timeout = date('YmdHis', strtotime("-" . $acl["cache_timeout"] . " Minutes"));

      $query = "
      SELECT
        `contact_id`
      FROM
        `civicrm_hierarchicalacl_tree_cache`
      WHERE `hierarchicalacl_id` = {$acl_id}
        AND `contact_id` = {$contactID}
        AND `cache_date` < '{$timeout}'";

      // if the query does not return the contact_id, it means the acl tree needs refresh
      $value = CRM_Core_DAO::singleValueQuery($query, []);
      return is_null($value);
    }
  }

  /**
   * Refreshes the Hierarchicalacl Tree cache date for a given user
   *
   * @param int $contactID
   * @param int $acl_id
   * @return void
   */
  public static function refreshTreeCache($contactID, $acl_id) {
    $sql = "
    REPLACE INTO `civicrm_hierarchicalacl_tree_cache`
      (`contact_id`, `hierarchicalacl_id`, `cache_date`)
    VALUES
      ({$contactID}, {$acl_id}, NOW())";
    CRM_Core_DAO::executeQuery($sql);
  }

  /**
   * Creates the Hierarchicalacl Tree for a given user
   *
   * @param int $contactID
   * @param int $type
   * @param array $acls
   * @return bool
   */
  public static function createTreeTable($contactID, $type, $acls) {
    if (self::$treeTable[$contactID]) {
      return TRUE;
    }
    else {
      $now = date('Y-m-d');
      foreach ($acls as $acl_id => $acl) {
        if (!$acl['is_active']) {
          continue;
        }
        if (!empty($acl['roles']) && !CRM_Core_Permission::checkGroupRole($acl['roles'])) {
          continue;
        }

        if (self::needsTreeCacheRefresh($contactID, $acl_id, $acl)) {
          $depth = 0;
          // Cleanup the tree
          $sql = "
          DELETE FROM `civicrm_hierarchicalacl_tree`
          WHERE `contact_id` = {$contactID}
            AND `hierarchicalacl_id`= {$acl_id}";
          CRM_Core_DAO::executeQuery($sql);

          // Insert depth 0, the logged in user
          $sql = "
          INSERT INTO `civicrm_hierarchicalacl_tree`
            (`hierarchicalacl_id`, `depth`, `contact_id`, `contact_id_a`, `relationship_type_id`)
          VALUES
            ({$acl_id}, {$depth}, {$contactID}, {$contactID}, NULL)";
          CRM_Core_DAO::executeQuery($sql);

          // Build the Hierarchical Contact tree based on the relationships acl
          foreach ($acl["hierarchy"] as $relationship) {
            $depth++;
            $sql = "INSERT INTO `civicrm_hierarchicalacl_tree`
              (`hierarchicalacl_id`, `depth`, `contact_id`, `contact_id_a`, `relationship_type_id`)
              SELECT DISTINCT
                {$acl_id},
                {$depth},
                {$contactID},
                rel.`contact_id_b`,
                " . (empty($relationship['relationship_type_id_contacts']) ? 'NULL' : $relationship['relationship_type_id_contacts']) . "
              FROM `civicrm_hierarchicalacl_tree` tree
              INNER JOIN `civicrm_relationship` rel
                ON rel.`contact_id_a` = tree.`contact_id_a`
                  AND tree.`depth` = " . ($depth - 1) . "
                  AND rel.`relationship_type_id` = " . $relationship['relationship_type_id_hierarchy'] . "
              WHERE tree.`contact_id` = {$contactID}
                AND tree.`hierarchicalacl_id`= {$acl_id}
                AND rel.`is_active` = 1
                AND (rel.`start_date` IS NULL OR rel.`start_date` <= '{$now}' )
                AND (rel.`end_date` IS NULL OR rel.`end_date` >= '{$now}')
            ";
            CRM_Core_DAO::executeQuery($sql);
          }

          // Updates cache timestamp
          self::refreshTreeCache($contactID, $acl_id);
        }
      }
      self::$treeTable[$contactID] = TRUE;
      return TRUE;
    }
  }

  /**
   * Creates permissions table with Contacts allowed to access
   *
   * @param int $contactID
   * @return string
   */
  public static function createPermissionsTable($contactID) {
    if (!empty(self::$permsTables[$contactID])) {
      return self::$permsTables[$contactID];
    }
    else {
      $tmpTable = CRM_Utils_SQL_TempTable::build()->setCategory('hacl')->setMemory();
      $tmpTableName = $tmpTable->getName();
      $tmpTable->drop();
      $tmpTable->createWithColumns('`contact_id` INT(10) NOT NULL, PRIMARY KEY (`contact_id`)');
      $now = date('Y-m-d');

      // Get all Contacts related with the hierarchical tree
      $sql = "
      INSERT INTO {$tmpTableName}
      SELECT
        DISTINCT rel.`contact_id_b`
      FROM `civicrm_hierarchicalacl_tree` tree
      INNER JOIN `civicrm_relationship` rel
        ON rel.`contact_id_a` = tree.`contact_id_a`
        AND rel.`relationship_type_id` = tree.`relationship_type_id`
      WHERE
        tree.`contact_id` = {$contactID}
        AND rel.`is_active` = 1
        AND (rel.`start_date` IS NULL OR rel.`start_date` <= '{$now}' )
        AND (rel.`end_date` IS NULL OR rel.`end_date` >= '{$now}')";
      CRM_Core_DAO::executeQuery($sql);

      // Add the Tree Contacts
      $sql = "
      REPLACE INTO {$tmpTableName}
      SELECT
        `contact_id_a`
      FROM `civicrm_hierarchicalacl_tree` tree
      WHERE
        tree.`contact_id` = {$contactID}";
      CRM_Core_DAO::executeQuery($sql);

      self::$permsTables[$contactID] = $tmpTableName;
      return $tmpTableName;
    }
  }

}
