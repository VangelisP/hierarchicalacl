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
          <li><strong>Contacts' Relationship Type</strong>: Select the Relationship Type that connects Contacts to be accessed with Contacts in this level</li><br/>
          <b>IMPORTANT:</b> Always the relationships are being considered from Contact A to Contact B point of view.
        </ul>
      </li>
      <li><strong>Roles</strong>: Apply this ACL to specific roles.</li>
    </ul>
  </div>
</div>

{include file="CRM/Jsoneditor/Jsoneditor.tpl"}

{include file="CRM/common/formButtons.tpl" location="bottom"}
