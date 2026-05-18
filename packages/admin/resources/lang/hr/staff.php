<?php

return [

    'label' => 'Osoblje',

    'plural_label' => 'Osoblje',

    'table' => [
        'firstname' => [
            'label' => 'Ime',
        ],
        'lastname' => [
            'label' => 'Prezime',
        ],
        'email' => [
            'label' => 'E-mail',
        ],
        'admin' => [
            'badge' => 'Super administrator',
        ],
    ],

    'form' => [
        'firstname' => [
            'label' => 'Ime',
        ],
        'lastname' => [
            'label' => 'Prezime',
        ],
        'email' => [
            'label' => 'E-mail',
        ],
        'password' => [
            'label' => 'Lozinka',
            'hint' => 'Ponovno postavi lozinku',
        ],
        'admin' => [
            'label' => 'Super administrator',
            'helper' => 'Uloge super administratora ne mogu se mijenjati u Hubu.',
        ],
        'roles' => [
            'label' => 'Uloge',
            'helper' => ':roles imaju puni pristup',
        ],
        'permissions' => [
            'label' => 'Dozvole',
        ],
        'role' => [
            'label' => 'Naziv uloge',
        ],
    ],

    'action' => [
        'acl' => [
            'label' => 'Kontrola pristupa',
        ],
        'add-role' => [
            'label' => 'Dodaj ulogu',
        ],
        'delete-role' => [
            'label' => 'Izbriši ulogu',
            'heading' => 'Izbriši ulogu: :role',
        ],
    ],

    'acl' => [
        'title' => 'Kontrola pristupa',
        'tooltip' => [
            'roles-included' => 'Dozvola je uključena u sljedeće uloge',
        ],
        'notification' => [
            'updated' => 'Ažurirano',
            'error' => 'Greška',
            'no-role' => 'Uloga nije registrirana u Lunaru',
            'no-permission' => 'Dozvola nije registrirana u Lunaru',
            'no-role-permission' => 'Uloga i dozvola nisu registrirane u Lunaru',
        ],
    ],

];
