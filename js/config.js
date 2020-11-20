CRM.$(document).ready(function(){
  var starting_value;
  if (CRM.vars.HierarchicalACL.config_json != ""){
    var starting_value = JSON.parse(CRM.vars.HierarchicalACL.config_json);
  }
  var options = {
    theme: "html",
    iconlib: "fontawesome4",
    startval: starting_value,
    disable_collapse: true,
    disable_properties: true,
    disable_array_reorder: true,
    disable_edit_json: true,
    disable_array_delete_last_row: true,
    prompt_before_delete: true,
    schema: {
      "type": "array",
      "title": "Hierarchical ACL",
      "format": "tabs",
      "items": {
        "title": "ACL",
        "headerTemplate": "{{ self.name }}",
        "type": "object",
        "required": [
        "name",
        "is_active",
        "cache_timeout",
        "hierarchy",
        "roles"
        ],
        "properties": {
          "name": {
            "type": "string",
            "minLength": 3,
            "default": "Hierarchical ACL"
          },
          "is_active": {
            "type": "boolean",
            "format": "checkbox",
            "title": "Is Active?"
          },
          "cache_timeout": {
            "type": "integer",
            "title": "Cache Timeout"
          },
          "hierarchy": {
            "type": "array",
            "format": "table",
            "title": " ",
            "items": {
              "type": "object",
              "title": "Relationships",
              "properties": {
                "depth":{
                  "title": "Depth",
                  "type": "integer",
                  "watch": {
                    "arr": "item"
                  },
                  "options": {
                    "inputAttributes": {
                      "style": "width: 25px"
                    }
                  },
                  "template": "arrayIndex"
                },
                "relationship_type_id_hierarchy": {
                  "type": "integer",
                  "title": "Parent Level Relationship Type",
                  "required": true,
                  "enum": CRM.HierarchicalACLConfig.relationship_types_ids_b_a,
                  'options': {
                    'enum_titles': CRM.HierarchicalACLConfig.relationship_types_labels_b_a
                  }
                },
                "relationship_type_id_contacts": {
                  "type": "integer",
                  "title": "Contacts' Relationship Type",
                  "required": true,
                  "enum": CRM.HierarchicalACLConfig.relationship_types_ids_a_b,
                  'options': {
                    'enum_titles': CRM.HierarchicalACLConfig.relationship_types_labels_a_b
                  }
                }
              }
            }
          },
          "roles": {
            "type": "array",
            "format": "select",
            "uniqueItems": true,
            "items": {
              "type": "string",
              "enum": CRM.HierarchicalACLConfig.roles
            }
          }
        }
      }
    }
  }
  var container = document.getElementById('jsoneditor');
  var editor = new JSONEditor(container, options);

  window.JSONEditor.defaults.callbacks.template = {
    arrayIndex: function(jseditor, e) {
      return jseditor.parent.key*1+1;
    }
  };

  editor.on('change',function() {
    CRM.$("#config_json").val(JSON.stringify(editor.getValue(), null, 2));
  });
});
