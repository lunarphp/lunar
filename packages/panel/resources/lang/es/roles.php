<?php

return [
    'title' => 'Roles',
    'description' => 'Group permissions into roles staff can hold.',
    'create_role' => 'Create role',
    'create_description' => 'Add a role; grant its permissions on the edit screen.',
    'first_party_badge' => 'Built-in',
    'empty_title' => 'No roles',

    'column_name' => 'Name',
    'column_permissions' => 'Permissions',
    'column_staff' => 'Staff',

    'field_name' => 'Name',
    'name_hint' => 'lower-case-and-dashes',
    'name_placeholder' => 'e.g. catalogue-manager',

    'section_permissions' => 'Permissions',
    'permissions_desc' => 'What staff holding this role can do. Admins hold every permission regardless of roles.',
    'edit_title' => 'Edit role — {name}',

    'confirm_delete' => 'Are you sure you want to delete this role?',
    'confirm_delete_title' => 'Delete role?',
    'confirm_delete_body' => '"{name}" will be permanently removed.',
    'delete_blocked_first_party' => 'Built-in roles cannot be deleted.',
    'delete_blocked_staff' => 'Cannot delete a role held by staff. Unassign it first.',

    'flash_created' => 'Role created.',
    'flash_updated' => 'Role updated.',
    'flash_deleted' => 'Role deleted.',
];
