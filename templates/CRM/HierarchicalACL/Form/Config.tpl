<div class="crm-section">
  <div id="help" class="description">
    <h4>Basic information</h4><br/>
    <ul>
      <li><strong>Name</strong>: Name to identify the ACL</li>
      <li><strong>Is Active</strong>: Check this to activate the ACL</li>
      <li><strong>Use Strict Relationship Permissions</strong>: Apply ACL value selected in each relationship permissions (<strong>None</strong> / <strong>View only</strong> / <strong>View and update</strong> ) strictly</li>
      <li>
        <strong>Cache Timeout</strong>: Number in minutes to refresh the hierarchical tree cache when the user access CiviCRM.<br />
        Zero value ("0") means no cache applied
      </li>
      <li><strong>Relationships</strong>: Add the Hierarchical Levels of ACLs based on Relationship Type
        <ul>
          <li><strong>Parent Level Relationship Type</strong>: Select the Relationship Type that connects Contacts of upper level, to this level</li>
          <li><strong>Contacts' Relationship Type</strong>: Select the Relationship Type that connects Contacts to be accessed with Contacts in this level</li>
        </ul>
      </li>
      <li><strong>Roles</strong>: Apply this ACL to specific roles.</li>
    </ul>
  </div>
</div>

<div class="crm-section">
  <div class="crm-section">
    <div id="jsoneditor" style="width: 90%; min-height: 200px;"></div>
    <div class="clear"></div>
  </div>
  <div align="right">
    <font size="-2" color="gray">
      Powered by <a href="https://github.com/json-editor/json-editor">JSON editor</a>.
    </font>
  </div>
</div>
{$form.config_json.html}

{include file="CRM/common/formButtons.tpl" location="bottom"}
