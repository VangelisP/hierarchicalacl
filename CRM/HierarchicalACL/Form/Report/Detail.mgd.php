<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
return [
  [
    'name' => 'CRM_HierarchicalACL_Form_Report_Detail',
    'entity' => 'ReportTemplate',
    'params' => [
      'version' => 3,
      'label' => 'Hierarchical ACL User Access Detail',
      'description' => 'Retrieves Contacts who can be accessed by specific User using the Hierarchical ACLs',
      'class_name' => 'CRM_HierarchicalACL_Form_Report_Detail',
      'report_url' => 'hierarchicalacl/detail',
      'component' => '',
    ],
  ],
];
