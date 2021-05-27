<?php
use CRM_Hierarchicalacl_ExtensionUtil as E;

class CRM_HierarchicalACL_Form_Report_Detail extends CRM_Report_Form {

  protected $_tmpPermsTableName = NULL;

  /**
   * Class constructor.
   */
  public function __construct() {
    // This Report is not for everybody!
    if (!CRM_Core_Permission::check('view all contacts')) {
      $url = CRM_Utils_System::url('civicrm/report/list', 'reset=1');
      CRM_Core_Error::statusBounce(ts('You do not have permission to access this report.'), $url);
    }

    $this->_columns = [
      'civicrm_contact' => [
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' => [
          'sort_name' => [
            'title' => ts('Contact Name'),
            'required' => TRUE,
          ],
          'contact_type' => [
            'title' => ts('Contact Type'),
          ],
          'contact_sub_type' => [
            'title' => ts('Contact Subtype'),
          ],
        ],
        'filters' => [],
        'grouping' => 'contact-fields',
        'order_bys' => [
          'sort_name' => [
            'title' => ts('Last Name, First Name'),
            'default' => '1',
            'default_weight' => '0',
            'default_order' => 'ASC',
          ],
          'first_name' => [
            'title' => ts('First Name'),
          ],
          'contact_type' => [
            'title' => ts('Contact Type'),
          ],
          'contact_sub_type' => [
            'title' => ts('Contact Subtype'),
          ],
        ],
      ],
      'civicrm_email' => [
        'dao' => 'CRM_Core_DAO_Email',
        'fields' => [
          'email' => [
            'title' => ts('Email'),
          ],
        ],
        'grouping' => 'contact-fields',
        'order_bys' => [
          'email' => [
            'title' => ts('Email'),
          ],
        ],
      ],
      'civicrm_hierarchicalacl' => [
        'fields' => [
          'hierarchicalacl_id' => [
            'title' => ts('ACL ID'),
          ],
          'depth' => [
            'title' => ts('Depth'),
          ],
        ],
        'grouping' => 'hierarchicalacl-fields',
        'order_bys' => [
          'hierarchicalacl_id' => [
            'title' => ts('ACL ID'),
          ],
          'depth' => [
            'title' => ts('Depth'),
          ],
        ],
        'filters' => [
          'contact_id' => [
            'title' => ts('Logged in User'),
            'operatorType' => CRM_Report_Form::OP_ENTITYREF,
            'type' => CRM_Utils_Type::T_INT,
            'attributes' => [
              'select' =>
              [
                'minimumInputLength' => 0,
                'multiple' => FALSE,
              ],
              'api' => [
                'params' => [
                  'contact_type' => 'Individual',
                ],
              ],
              'entity' => 'contact',
            ],
            'pseudofield' => TRUE,
          ],
          'hierarchicalacl_id' => [
            'title' => ts('ACL ID'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => array_column(CRM_HierarchicalACL_BAO_HierarchicalACL::getHierarchicalACLs(), 'name'),
            'default' => NULL,
            'type' => CRM_Utils_Type::T_STRING,
            'pseudofield' => TRUE,
          ],
          'depth' => [
            'title' => ts('Depth'),
            'type' => CRM_Utils_Type::T_INT,
          ],
        ],
      ],
    ];
    parent::__construct();
  }

  /**
   * Validate incompatible report settings.
   *
   * @return bool
   *   true if no error found
   */
  public function validate() {
    $contactID = $this->getElementValue('contact_id_value');
    if (empty($contactID)) {
      $this->setElementError('contact_id_op', ts('You must choose Logged in user to check its HierarchicalACL results'));
    }

    return parent::validate();
  }

  /**
   * Shared function for preliminary processing.
   *
   */
  public function beginPostProcessCommon() {
    $contactID = $this->_params['contact_id_value'];
    $aclsID = $this->_params['hierarchicalacl_id_value'];
    $acls = CRM_HierarchicalACL_BAO_HierarchicalACL::getHierarchicalACLs($aclsID);
    CRM_HierarchicalACL_BAO_HierarchicalACL::createTreeTable($contactID, NULL, $acls, TRUE);
    $this->_tmpPermsTableName = CRM_HierarchicalACL_BAO_HierarchicalACL::createPermissionsTable($contactID, $type, $acls, TRUE);
  }

  /**
   * Set the FROM clause for the report.
   */
  public function from() {
    $this->_from = "
      FROM
        civicrm_contact {$this->_aliases['civicrm_contact']}
      INNER JOIN
        {$this->_tmpPermsTableName} hierarchicalacl_civireport ON {$this->_aliases['civicrm_contact']}.id = hierarchicalacl_civireport.contact_id";

    $this->joinEmailFromContact();
  }

}
