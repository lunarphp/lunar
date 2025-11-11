<?php

return [

    'label' => 'Munkatárs',

    'plural_label' => 'Munkatársak',

    'table' => [
        'first_name' => [
            'label' => 'Keresztnév',
        ],
        'last_name' => [
            'label' => 'Vezetéknév',
        ],
        'email' => [
            'label' => 'E-mail',
        ],
        'admin' => [
            'badge' => 'Főadminisztrátor',
        ],
    ],

    'form' => [
        'first_name' => [
            'label' => 'Keresztnév',
        ],
        'last_name' => [
            'label' => 'Vezetéknév',
        ],
        'email' => [
            'label' => 'E-mail',
        ],
        'password' => [
            'label' => 'Jelszó',
            'hint' => 'Jelszó visszaállítása',
        ],
        'admin' => [
            'label' => 'Főadminisztrátor',
            'helper' => 'A főadminisztrátor szerepkörök nem változtathatók a hubban.',
        ],
        'roles' => [
            'label' => 'Szerepkörök',
            'helper' => ':roles teljes hozzáféréssel rendelkeznek',
        ],
        'permissions' => [
            'label' => 'Jogosultságok',
        ],
        'role' => [
            'label' => 'Szerepkör neve',
        ],
    ],

    'action' => [
        'acl' => [
            'label' => 'Hozzáférés-vezérlés',
        ],
        'add-role' => [
            'label' => 'Szerepkör hozzáadása',
        ],
        'delete-role' => [
            'label' => 'Szerepkör törlése',
            'heading' => 'Szerepkör törlése: :role',
        ],
    ],

    'acl' => [
        'title' => 'Hozzáférés-vezérlés',
        'tooltip' => [
            'roles-included' => 'A jogosultság a következő szerepkörökben szerepel',
        ],
        'notification' => [
            'updated' => 'Frissítve',
            'error' => 'Hiba',
            'no-role' => 'A szerepkör nincs regisztrálva a Lunarban',
            'no-permission' => 'A jogosultság nincs regisztrálva a Lunarban',
            'no-role-permission' => 'Szerepkör és jogosultság nincs regisztrálva a Lunarban',
        ],
    ],

];
