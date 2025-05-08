<?php

return [

    'label' => 'Staff',

    'plural_label' => 'Staff',

    'table' => [
        'first_name' => [
            'label' => 'First Name',
        ],
        'last_name' => [
            'label' => 'Last Name',
        ],
        'email' => [
            'label' => 'Email',
        ],
        'admin' => [
            'badge' => 'Super Admin',
        ],
    ],

    'form' => [
        'first_name' => [
            'label' => 'First Name',
        ],
        'last_name' => [
            'label' => 'Last Name',
        ],
        'email' => [
            'label' => 'Email',
        ],
        'password' => [
            'label' => 'Password',
            'hint' => 'Reset password',
        ],
        'admin' => [
            'label' => 'Super Admin',
            'helper' => 'Super admin roles cannot be changed in the hub.',
        ],
        'roles' => [
            'label' => 'Roles',
            'helper' => ':roles have full access',
        ],
        'permissions' => [
            'label' => 'Permissions',
        ],
        'role' => [
            'label' => 'Role Name',
        ],
    ],

    'action' => [
        'acl' => [
            'label' => 'Access Control',
        ],
        'add-role' => [
            'label' => 'Add Role',
        ],
        'delete-role' => [
            'label' => 'Delete Role',
            'heading' => 'Delete role: :role',
        ],
    ],

    'acl' => [
        'title' => 'Access Control',
        'tooltip' => [
            'roles-included' => 'Permission is included in following roles',
        ],
        'notification' => [
            'updated' => 'Updated',
            'error' => 'Error',
            'no-role' => 'Role not registered in Lunar',
            'no-permission' => 'Permission not registered in Lunar',
            'no-role-permission' => 'Role and Permission not registered in Lunar',
        ],
    ],

];
