<?php

return [
    'title' => 'Staff',
    'description' => 'The people who can sign in to this panel.',
    'create_staff' => 'Create staff member',
    'create_description' => 'Add someone who can sign in to this panel.',
    'admin_badge' => 'Admin',
    'empty_title' => 'No staff',
    'search_placeholder' => 'Search staff…',

    'column_name' => 'Name',
    'column_email' => 'Email',
    'column_roles' => 'Roles',

    'field_first_name' => 'First name',
    'field_last_name' => 'Last name',
    'field_email' => 'Email',
    'field_password' => 'Password',
    'password_hint' => 'leave blank to keep the current password',
    'field_roles' => 'Roles',
    'admin' => 'Admin',
    'admin_hint' => 'Admins hold every permission automatically.',
    'last_admin_hint' => 'This is the last admin. Make someone else an admin first.',
    'last_admin_blocked' => 'Cannot remove the admin flag from the last admin. Make someone else an admin first.',

    'section_details' => 'Details',
    'section_access' => 'Access',
    'edit_title' => 'Edit staff member — {name}',

    'confirm_delete' => 'Are you sure you want to delete this staff member?',
    'confirm_delete_title' => 'Delete staff member?',
    'confirm_delete_body' => '{name} will no longer be able to sign in.',
    'delete_blocked_self' => 'You cannot delete your own account.',
    'delete_blocked_last_admin' => 'Cannot delete the last admin. Make someone else an admin first.',

    'flash_created' => 'Staff member created.',
    'flash_updated' => 'Staff member updated.',
    'flash_deleted' => 'Staff member deleted.',
];
