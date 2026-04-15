<?php

return [

    'label' => 'Personeel',

    'plural_label' => 'Personeel',

    'table' => [
        'first_name' => [
            'label' => 'Voornaam',
        ],
        'last_name' => [
            'label' => 'Achternaam',
        ],
        'email' => [
            'label' => 'E-mail',
        ],
        'admin' => [
            'badge' => 'Super Admin',
        ],
    ],

    'form' => [
        'first_name' => [
            'label' => 'Voornaam',
        ],
        'last_name' => [
            'label' => 'Achternaam',
        ],
        'email' => [
            'label' => 'E-mail',
        ],
        'password' => [
            'label' => 'Wachtwoord',
            'hint' => 'Wachtwoord resetten',
        ],
        'admin' => [
            'label' => 'Super Admin',
            'helper' => 'Super admin rollen kunnen niet worden gewijzigd in de hub.',
        ],
        'roles' => [
            'label' => 'Rollen',
            'helper' => ':roles hebben volledige toegang',
        ],
        'permissions' => [
            'label' => 'Machtigingen',
        ],
        'role' => [
            'label' => 'Rolnaam',
        ],
    ],

    'action' => [
        'acl' => [
            'label' => 'Toegangscontrole',
        ],
        'add-role' => [
            'label' => 'Rol toevoegen',
        ],
        'delete-role' => [
            'label' => 'Rol verwijderen',
            'heading' => 'Rol verwijderen: :role',
        ],
    ],

    'acl' => [
        'title' => 'Toegangscontrole',
        'tooltip' => [
            'roles-included' => 'Machtiging is inbegrepen in de volgende rollen',
        ],
        'notification' => [
            'updated' => 'Bijgewerkt',
            'error' => 'Fout',
            'no-role' => 'Rol niet geregistreerd in Lunar',
            'no-permission' => 'Machtiging niet geregistreerd in Lunar',
            'no-role-permission' => 'Rol en Machtiging niet geregistreerd in Lunar',
        ],
    ],

];
