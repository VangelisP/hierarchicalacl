DROP TABLE IF EXISTS `civicrm_hierarchicalacl_tree`;
DROP TABLE IF EXISTS `civicrm_hierarchicalacl_tree_cache`;

DELETE FROM `civicrm_settings` WHERE `name` = 'hierarchicalacl_config';
