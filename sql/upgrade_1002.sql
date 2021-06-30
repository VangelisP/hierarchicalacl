/**
  Regenerate `civicrm_hierarchicalacl_tree_cache` table
**/
DROP TABLE IF EXISTS `civicrm_hierarchicalacl_tree_cache`;

CREATE TABLE `civicrm_hierarchicalacl_tree_cache` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contact_id` INT(10) UNSIGNED NOT NULL,
  `hierarchicalacl_id` INT(10) NOT NULL,
  `cache_date` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contact_id` (`contact_id`),
  CONSTRAINT `civicrm_hierarchicalacl_tree_cache_ibfk_1` FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

