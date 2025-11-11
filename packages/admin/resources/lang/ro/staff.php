<?php

return [

    'label' => 'Personal',

    'plural_label' => 'Personal',

    'table' => [
        'first_name' => [
            'label' => 'Prenume',
        ],
        'last_name' => [
            'label' => 'Nume',
        ],
        'email' => [
            'label' => 'E-mail',
        ],
        'admin' => [
            'badge' => 'Super administrator',
        ],
    ],

    'form' => [
        'first_name' => [
            'label' => 'Prenume',
        ],
        'last_name' => [
            'label' => 'Nume',
        ],
        'email' => [
            'label' => 'E-mail',
        ],
        'password' => [
            'label' => 'Parolă',
            'hint' => 'Resetează parola',
        ],
        'admin' => [
            'label' => 'Super administrator',
            'helper' => 'Rolurile de super administrator nu pot fi schimbate în hub.',
        ],
        'roles' => [
            'label' => 'Roluri',
            'helper' => ':roles au acces complet',
        ],
        'permissions' => [
            'label' => 'Permisiuni',
        ],
        'role' => [
            'label' => 'Nume rol',
        ],
    ],

    'action' => [
        'acl' => [
            'label' => 'Control acces',
        ],
        'add-role' => [
            'label' => 'Adaugă rol',
        ],
        'delete-role' => [
            'label' => 'Șterge rol',
            'heading' => 'Șterge rolul: :role',
        ],
    ],

    'acl' => [
        'title' => 'Control acces',
        'tooltip' => [
            'roles-included' => 'Permisiunea este inclusă în următoarele roluri',
        ],
        'notification' => [
            'updated' => 'Actualizat',
            'error' => 'Eroare',
            'no-role' => 'Rolul nu este înregistrat în Lunar',
            'no-permission' => 'Permisiunea nu este înregistrată în Lunar',
            'no-role-permission' => 'Rolul și permisiunea nu sunt înregistrate în Lunar',
        ],
    ],

];
