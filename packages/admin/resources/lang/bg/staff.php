<?php

return [

    'label' => 'Персонал',

    'plural_label' => 'Персонал',

    'table' => [
        'first_name' => [
            'label' => 'Име',
        ],
        'last_name' => [
            'label' => 'Фамилия',
        ],
        'email' => [
            'label' => 'Имейл',
        ],
        'admin' => [
            'badge' => 'Супер администратор',
        ],
    ],

    'form' => [
        'first_name' => [
            'label' => 'Име',
        ],
        'last_name' => [
            'label' => 'Фамилия',
        ],
        'email' => [
            'label' => 'Имейл',
        ],
        'password' => [
            'label' => 'Парола',
            'hint' => 'Нулиране на паролата',
        ],
        'admin' => [
            'label' => 'Супер администратор',
            'helper' => 'Ролите на супер администратора не могат да бъдат променяни в хъба.',
        ],
        'roles' => [
            'label' => 'Роли',
            'helper' => ':roles имат пълен достъп',
        ],
        'permissions' => [
            'label' => 'Разрешения',
        ],
        'role' => [
            'label' => 'Име на роля',
        ],
    ],

    'action' => [
        'acl' => [
            'label' => 'Контрол на достъпа',
        ],
        'add-role' => [
            'label' => 'Добави роля',
        ],
        'delete-role' => [
            'label' => 'Изтрий роля',
            'heading' => 'Изтрий роля: :role',
        ],
    ],

    'acl' => [
        'title' => 'Контрол на достъпа',
        'tooltip' => [
            'roles-included' => 'Разрешението е включено в следните роли',
        ],
        'notification' => [
            'updated' => 'Актуализирано',
            'error' => 'Грешка',
            'no-role' => 'Ролята не е регистрирана в Lunar',
            'no-permission' => 'Разрешението не е регистрирано в Lunar',
            'no-role-permission' => 'Ролята и разрешението не са регистрирани в Lunar',
        ],
    ],

];
