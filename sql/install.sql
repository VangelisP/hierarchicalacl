CREATE TABLE `civicrm_hierarchicalacl_tree` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `hierarchicalacl_id` INT(10) UNSIGNED NOT NULL,
  `depth` SMALLINT(5) UNSIGNED NOT NULL,
  `contact_id` INT(10) UNSIGNED NOT NULL,
  `contact_id_a` INT(10) UNSIGNED NOT NULL,
  `relationship_type_id` INT(10) UNSIGNED DEFAULT NULL,
  `is_permission_a_b` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `contact_id` (`contact_id`),
  KEY `contact_id_a` (`contact_id_a`),
  KEY `relationship_type_id` (`relationship_type_id`),
  CONSTRAINT `civicrm_hierarchicalacl_tree_ibfk_1` FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact` (`id`) ON DELETE CASCADE,
  CONSTRAINT `civicrm_hierarchicalacl_tree_ibfk_2` FOREIGN KEY (`contact_id_a`) REFERENCES `civicrm_contact` (`id`) ON DELETE CASCADE,
  CONSTRAINT `civicrm_hierarchicalacl_tree_ibfk_3` FOREIGN KEY (`relationship_type_id`) REFERENCES `civicrm_relationship_type` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `civicrm_hierarchicalacl_tree_cache` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contact_id` INT(10) UNSIGNED NOT NULL,
  `hierarchicalacl_id` INT(10) NOT NULL,
  `cache_date` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contact_id` (`contact_id`),
  CONSTRAINT `civicrm_hierarchicalacl_tree_cache_ibfk_1` FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
