<?php

return [

    'label' => 'Personal',

    'plural_label' => 'Personal',

    'table' => [
        'first_name' => [
            'label' => 'Nombre',
        ],
        'last_name' => [
            'label' => 'Apellido',
        ],
        'email' => [
            'label' => 'Correo Electrónico',
        ],
        'admin' => [
            'badge' => 'Super Admin',
        ],
    ],

    'form' => [
        'first_name' => [
            'label' => 'Nombre',
        ],
        'last_name' => [
            'label' => 'Apellido',
        ],
        'email' => [
            'label' => 'Correo Electrónico',
        ],
        'password' => [
            'label' => 'Contraseña',
            'hint' => 'Restablecer contraseña',
        ],
        'admin' => [
            'label' => 'Super Admin',
            'helper' => 'Los roles de super admin no se pueden cambiar en el hub.',
        ],
        'roles' => [
            'label' => 'Roles',
            'helper' => ':roles tienen acceso completo',
        ],
        'permissions' => [
            'label' => 'Permisos',
        ],
        'role' => [
            'label' => 'Nombre del Rol',
        ],
    ],

    'action' => [
        'acl' => [
            'label' => 'Control de Acceso',
        ],
        'add-role' => [
            'label' => 'Agregar Rol',
        ],
        'delete-role' => [
            'label' => 'Eliminar Rol',
            'heading' => 'Eliminar rol: :role',
        ],
    ],

    'acl' => [
        'title' => 'Control de Acceso',
        'tooltip' => [
            'roles-included' => 'El permiso está incluido en los siguientes roles',
        ],
        'notification' => [
            'updated' => 'Actualizado',
            'error' => 'Error',
            'no-role' => 'Rol no registrado en Lunar',
            'no-permission' => 'Permiso no registrado en Lunar',
            'no-role-permission' => 'Rol y Permiso no registrados en Lunar',
        ],
    ],

];
