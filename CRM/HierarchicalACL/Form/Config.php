<?php

use CRM_HierarchicalACL_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_HierarchicalACL_Form_Config extends CRM_Core_Form {

  public function buildQuickForm() {
    CRM_Utils_System::setTitle(E::ts('HierarchicalACL config'));
    $this->addElement('hidden', 'config_json', NULL, array('id' => 'config_json'));
    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Save'),
        'isDefault' => TRUE,
      ),
    ));

    $relationship_types_a_b = [];
    $relationship_types_b_a = [];
    foreach (CRM_Core_PseudoConstant::relationshipType('label') as $relationshipType) {
      $relationship_types_a_b[$relationshipType['id']] = $relationshipType['label_a_b'];
      $relationship_types_b_a[$relationshipType['id']] = $relationshipType['label_b_a'];
    }
    $relationship_types_a_b = array_merge([0 => E::ts("-- Restrict Access --")], $relationship_types_a_b);

    CRM_Core_Resources::singleton()
      ->addScriptUrl('https://cdn.jsdelivr.net/npm/@json-editor/json-editor@2.5.1/dist/jsoneditor.min.js')
      ->addScriptFile('hierarchicalacl', 'js/config.js')
      ->addStyleUrl('//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.0.3/css/font-awesome.css')
      ->addStyleFile('hierarchicalacl', 'css/config.css')
      ->addSetting([
        'HierarchicalACLConfig' => [
          'relationship_types_ids_a_b' => array_keys($relationship_types_a_b),
          'relationship_types_ids_b_a' => array_keys($relationship_types_b_a),
          'relationship_types_labels_a_b' => array_values($relationship_types_a_b),
          'relationship_types_labels_b_a' => array_values($relationship_types_b_a),
          'roles' => array_keys(CRM_Core_Config::singleton()->userSystem->getRoleNames())
        ],
      ]);
    parent::buildQuickForm();
  }

  public function setDefaultValues() {
    $defaults = parent::setDefaultValues();
    $config_json = Civi::settings()->get('hierarchicalacl_config');
    if (!empty($config_json)) {
      $config_json = json_encode($config_json);
    }
    else {
      $config_json = '';
    }
    CRM_Core_Resources::singleton()->addVars('HierarchicalACL', ['config_json' => $config_json]);

    return $defaults;
  }

  public function postProcess() {
    $params = $this->exportValues();
    Civi::settings()->set('hierarchicalacl_config', json_decode($params['config_json'], TRUE));
    CRM_Core_Session::setStatus(E::ts("Hierarchical ACL configuration has been saved."), ts('Saved'), 'success');
    parent::postProcess();

    $url = CRM_Utils_System::url('civicrm/admin');
    CRM_Utils_System::redirect($url);
  }

}