<?php

use CRM_HierarchicalACL_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_HierarchicalACL_Form_Config extends CRM_Core_Form {
  protected $_cleancacheButtonName;
  protected $_editor;

  /**
   * Form pre-processing.
   */
  public function preProcess() {
    $this->_editor = new \Civi\Jsoneditor\Jsoneditor('hierarchicalacl', $this);
    parent::preProcess();
  }

  public function buildQuickForm() {
    CRM_Utils_System::setTitle(E::ts('HierarchicalACL config'));
    $this->_cleancacheButtonName = $this->getButtonName('next', 'cleanup');
    $relationship_types_a_b = [];
    $relationship_types_b_a = [];
    foreach (CRM_Core_PseudoConstant::relationshipType('label') as $relationshipType) {
      $relationship_types_a_b[$relationshipType['id']] = $relationshipType['label_a_b'];
      $relationship_types_b_a[$relationshipType['id']] = $relationshipType['label_b_a'];
    }
    $relationship_types_a_b = [0 => E::ts("-- Restrict Access --")] + $relationship_types_a_b;

    CRM_Core_Resources::singleton()
      ->addScriptFile('hierarchicalacl', 'js/hierarchicalacl.js')
      ->addStyleFile('hierarchicalacl', 'css/hierarchicalacl.css');

    $schema = [
      'type' => 'array',
      'title' => 'Hierarchical ACL',
      'format' => 'tabs',
      'items' => [
        'title' => 'ACL',
        'headerTemplate' => '{{ self.name }}',
        'type' => 'object',
        'required' => [
          'name',
          'is_active',
          'use_relationship_perms',
          'cache_timeout',
          'hierarchy',
          'roles',
        ],
        'properties' => [
          'name' => [
            'type' => 'string',
            'title' => 'Name',
            'minLength' => 3,
            'default' => 'Hierarchical ACL',
            'options' => [
              'inputAttributes' => [
                'style' => 'width: 550px',
              ],
            ],
          ],
          'is_active' => [
            'type' => 'boolean',
            'format' => 'checkbox',
            'title' => 'Is Active?',
          ],
          'use_relationship_perms' => [
            'type' => 'boolean',
            'format' => 'checkbox',
            'title' => 'Use Strict Relationship Permissions',
          ],
          'cache_timeout' => [
            'type' => 'integer',
            'title' => 'Cache Timeout',
            'options' => [
              'inputAttributes' => [
                'style' => 'width: 50px',
              ],
            ],
          ],
          'hierarchy' => [
            'type' => 'array',
            'format' => 'table',
            'title' => 'ACL Hierarchy',
            'items' => [
              'type' => 'object',
              'title' => 'Relationships',
              'properties' => [
                'depth' => [
                  'title' => 'Depth',
                  'type' => 'integer',
                  'watch' => [
                    'arr' => 'item',
                  ],
                  'options' => [
                    'inputAttributes' => [
                      'style' => 'width: 25px',
                    ],
                  ],
                  'template' => 'arrayIndex',
                ],
                'relationship_type_id_hierarchy' => [
                  'type' => 'integer',
                  'title' => 'Parent Level Relationship Type',
                  'required' => TRUE,
                  'enum' => array_keys($relationship_types_b_a),
                  'options' => [
                    'enum_titles' => array_values($relationship_types_b_a),
                  ],
                ],
                'relationship_type_id_contacts' => [
                  'type' => 'integer',
                  'title' => 'Contacts Relationship Type',
                  'required' => TRUE,
                  'enum' => array_keys($relationship_types_a_b),
                  'options' => [
                    'enum_titles' => array_values($relationship_types_a_b),
                  ],
                ],
              ],
            ],
          ],
          'roles' => [
            'type' => 'array',
            'format' => 'select',
            'uniqueItems' => TRUE,
            'items' => [
              'type' => 'string',
              'enum' => array_keys(CRM_Core_Config::singleton()->userSystem->getRoleNames()),
            ],
          ],
        ],
      ],
    ];
    $this->_editor->setConfig('theme', 'bootstrap4');
    $this->_editor->setConfig('iconlib', 'fontawesome4');
    $this->_editor->setConfig('disable_array_delete_last_row', TRUE);
    $this->_editor->setConfig('disable_array_reorder', TRUE);
    $this->_editor->setConfig('disable_collapse', TRUE);
    $this->_editor->setConfig('disable_edit_json', TRUE);
    $this->_editor->setConfig('disable_properties', TRUE);
    $this->_editor->setConfig('prompt_before_delete', TRUE);
    $this->_editor->setConfig('schema', $schema);
    $this->_editor->showOptions = TRUE;
    $this->_editor->showFooter = TRUE;
    $this->_editor->add();

    $this->addButtons([
      [
        'type' => 'submit',
        'name' => E::ts('Save'),
        'isDefault' => TRUE,
      ],
      [
        'type' => 'next',
        'name' => ts('Cleanup Caches'),
        'subName' => 'cleanup',
        'icon' => 'fa-undo',

      ],
    ]);

    parent::buildQuickForm();
  }

  public function postProcess() {
    $params = $this->exportValues();
    $buttonName = $this->controller->getButtonName();
    // check if cleanup button
    if ($buttonName == $this->_cleancacheButtonName) {
      CRM_HierarchicalACL_BAO_HierarchicalACL::dropTreeTable();
      CRM_Core_Session::setStatus(E::ts("Hierarchical ACL Cache has been cleaned up!"), ts('Saved'), 'success');
    }
    else {
      $this->_editor->save();
      // ACLs have changed we need to drop tree just in case
      CRM_HierarchicalACL_BAO_HierarchicalACL::dropTreeTable();

      parent::postProcess();
      CRM_Core_Session::setStatus(E::ts("Hierarchical ACL configuration has been saved."), ts('Saved'), 'success');
    }
    $url = CRM_Utils_System::url('civicrm/admin/hierarchicalacl/config?reset=1', 'reset=1');
    CRM_Utils_System::redirect($url);
  }

}
