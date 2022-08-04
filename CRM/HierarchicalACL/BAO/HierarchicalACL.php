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
  public static function getHierarchicalACLs($acl_ids = []) {
    if (!isset(self::$config)) {
      self::$config = \Civi\Jsoneditor\Jsoneditor::getSetting('hierarchicalacl', 'json');
    }

    // return some acls (for reports)
    if (!empty($acl_ids)) {
      return array_intersect_key(self::$config, array_flip($acl_ids));
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
        AND `cache_date` > '{$timeout}'";

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
   * @param int $date
   * @return void
   */
  public static function refreshTreeCache($contactID, $acl_id, $date) {
    $sql = "DELETE FROM `civicrm_hierarchicalacl_tree_cache` WHERE contact_id = {$contactID} AND `hierarchicalacl_id` = {$acl_id};";
    CRM_Core_DAO::executeQuery($sql);
    $sql = "INSERT INTO `civicrm_hierarchicalacl_tree_cache` (`contact_id`, `hierarchicalacl_id`, `cache_date`) VALUES ({$contactID}, {$acl_id}, '{$date}')";
    CRM_Core_DAO::executeQuery($sql);
  }

  /**
   * Truncate the Hierarchicalacl Tree and Cache
   *
   * @param array $cids
   *
   * @return void
   */
  public static function dropTreeTable($cids = []) {
    if (!empty($cids)) {
      $where = " WHERE contact_id IN (" . explode(",", $cids) . ") ";
      $sql = "DELETE FROM `civicrm_hierarchicalacl_tree_cache` " . $where;
      CRM_Core_DAO::executeQuery($sql);
      $sql = "DELETE FROM `civicrm_hierarchicalacl_tree` " . $where;
      CRM_Core_DAO::executeQuery($sql);
    }
    else {
      $sql = "TRUNCATE TABLE `civicrm_hierarchicalacl_tree_cache` ";
      CRM_Core_DAO::executeQuery($sql);
      $sql = "TRUNCATE TABLE `civicrm_hierarchicalacl_tree` ";
      CRM_Core_DAO::executeQuery($sql);
    }

    // reset ACL cache
    CRM_ACL_BAO_Cache::resetCache();
  }

  /**
   * Creates the Hierarchicalacl Tree for a given user
   *
   * @param int $contactID
   * @param int $type
   * @param array $acls
   * @param bool $skipRoleCheck
   * @return bool
   */
  public static function createTreeTable($contactID, $type, $acls, $skipRoleCheck = FALSE) {
    if (self::$treeTable[$contactID]) {
      return TRUE;
    }
    else {
      $now = date('YmdHis');
      foreach ($acls as $acl_id => $acl) {
        if (!$acl['is_active']) {
          continue;
        }
        if (!$skipRoleCheck && !empty($acl['roles']) && !CRM_Core_Permission::checkGroupRole($acl['roles'])) {
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
            (`hierarchicalacl_id`, `depth`, `contact_id`, `contact_id_a`, `relationship_type_id`, `is_permission_a_b`)
          VALUES
            ({$acl_id}, {$depth}, {$contactID}, {$contactID}, NULL, " . CRM_Core_Permission::EDIT . ")";
          CRM_Core_DAO::executeQuery($sql);

          // Build the Hierarchical Contact tree based on the relationships acl
          foreach ($acl["hierarchy"] as $relationship) {
            $depth++;
            $sql = "INSERT INTO `civicrm_hierarchicalacl_tree`
              (`hierarchicalacl_id`, `depth`, `contact_id`, `contact_id_a`, `relationship_type_id`, `is_permission_a_b`)
              SELECT DISTINCT
                {$acl_id},
                {$depth},
                {$contactID},
                rel.`contact_id_b`,
                " . (empty($relationship['relationship_type_id_contacts']) ? 'NULL' : $relationship['relationship_type_id_contacts']) . ",
                rel.`is_permission_a_b`
              FROM `civicrm_hierarchicalacl_tree` tree
              INNER JOIN `civicrm_relationship` rel
                ON rel.`contact_id_a` = tree.`contact_id_a`
                  AND rel.`relationship_type_id` = " . $relationship['relationship_type_id_hierarchy'] . "
              WHERE tree.`contact_id` = {$contactID}
                AND tree.`depth` = " . ($depth - 1) . "
                AND tree.`hierarchicalacl_id`= {$acl_id}
                AND rel.`is_active` = 1
                AND (rel.`start_date` IS NULL OR rel.`start_date` <= '{$now}' )
                AND (rel.`end_date` IS NULL OR rel.`end_date` >= '{$now}')";
            CRM_Core_DAO::executeQuery($sql);
          }

          // Updates cache timestamp
          self::refreshTreeCache($contactID, $acl_id, $now);
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
   * @param int $type
   * @param array $acls
   * @param bool $keepAclFields
   * @return string
   */
  public static function createPermissionsTable($contactID, $type, $acls, $keepAclFields = FALSE) {
    if (!empty(self::$permsTables[$contactID][$type])) {
      return self::$permsTables[$contactID][$type];
    }
    else {
      $tmpFields = [
        "`id` INT UNSIGNED NOT NULL AUTO_INCREMENT",
        "`contact_id` INT UNSIGNED NOT NULL",
      ];
      if ($keepAclFields) {
        $tmpFields[] = "`hierarchicalacl_id` INT(10) UNSIGNED NOT NULL";
        $tmpFields[] = "`depth` SMALLINT(5) UNSIGNED NOT NULL";
      }
      $tmpFields[] = "PRIMARY KEY (`id`), INDEX UI_contact (contact_id)";

      $tmpTable = CRM_Utils_SQL_TempTable::build()->setCategory('hacl')->setMemory();
      $tmpTableName = $tmpTable->getName();
      $tmpTable->drop();
      $tmpTable->createWithColumns(implode(",", $tmpFields));
      $now = date('YmdHis');

      foreach ($acls as $acl_id => $acl) {
        // Use strict relationship permissions condition
        if (!empty($acl["use_relationship_perms"])) {
          if ($type == CRM_Core_Permission::VIEW) {
            $permissionClause = " IN ( " . CRM_Core_Permission::EDIT . " , " . CRM_Core_Permission::VIEW . " ) ";
          }
          else {
            $permissionClause = " = " . CRM_Core_Permission::EDIT;
          }
          $relClause = " AND rel.`is_permission_a_b` " . $permissionClause;
          $treeClause = " AND tree.`is_permission_a_b` " . $permissionClause;
        }

        $sqlSelectFields = ["DISTINCT rel.`contact_id_b`"];
        $sqlInsertFields = ["`contact_id`"];
        if ($keepAclFields) {
          $sqlSelectFields[] = $acl_id;
          $sqlSelectFields[] = "`tree`.`depth`";

          $sqlInsertFields[] = "`hierarchicalacl_id`";
          $sqlInsertFields[] = "`depth`";
        }

        // Get all Contacts related with the hierarchical tree
        $sql = "
        INSERT INTO {$tmpTableName}
          (" . implode(",", $sqlInsertFields) . ")
        SELECT
          " . implode(",", $sqlSelectFields) . "
        FROM `civicrm_hierarchicalacl_tree` tree
        INNER JOIN `civicrm_relationship` rel
          ON rel.`contact_id_a` = tree.`contact_id_a`
          AND rel.`relationship_type_id` = tree.`relationship_type_id`
        WHERE
          tree.`contact_id` = {$contactID}
          AND tree.`hierarchicalacl_id`= {$acl_id}
          AND rel.`is_active` = 1
          AND (rel.`start_date` IS NULL OR rel.`start_date` <= '{$now}' )
          AND (rel.`end_date` IS NULL OR rel.`end_date` >= '{$now}')
        " . $relClause;
        CRM_Core_DAO::executeQuery($sql);
      }

      // Add the Tree Contacts
      $sqlSelectFields[0] = "DISTINCT tree.`contact_id_a`";
      $sql = "
      INSERT INTO {$tmpTableName}
        (" . implode(",", $sqlInsertFields) . ")
      SELECT
        " . implode(",", $sqlSelectFields) . "
      FROM `civicrm_hierarchicalacl_tree` tree
      WHERE
        tree.`contact_id` = {$contactID}
      " . $treeClause;
      CRM_Core_DAO::executeQuery($sql);

      self::$permsTables[$contactID][$type] = $tmpTableName;
      return $tmpTableName;
    }
  }

}
