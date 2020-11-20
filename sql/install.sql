CREATE TABLE `civicrm_hierarchicalacl_tree` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hierarchicalacl_id` int(10) unsigned NOT NULL,
  `depth` smallint(5) unsigned NOT NULL,
  `contact_id` int(10) unsigned NOT NULL,
  `contact_id_a` int(10) unsigned NOT NULL,
  `relationship_type_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contact_id` (`contact_id`),
  KEY `contact_id_a` (`contact_id_a`),
  KEY `relationship_type_id` (`relationship_type_id`),
  CONSTRAINT `civicrm_hierarchicalacl_tree_ibfk_1` FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact` (`id`) ON DELETE CASCADE,
  CONSTRAINT `civicrm_hierarchicalacl_tree_ibfk_2` FOREIGN KEY (`contact_id_a`) REFERENCES `civicrm_contact` (`id`) ON DELETE CASCADE,
  CONSTRAINT `civicrm_hierarchicalacl_tree_ibfk_3` FOREIGN KEY (`relationship_type_id`) REFERENCES `civicrm_relationship_type` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `civicrm_hierarchicalacl_tree_cache` (
  `contact_id` int(10) unsigned NOT NULL,
  `hierarchicalacl_id` int(10) NOT NULL,
  `cache_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`contact_id`),
  CONSTRAINT `civicrm_hierarchicalacl_tree_cache_ibfk_1` FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
