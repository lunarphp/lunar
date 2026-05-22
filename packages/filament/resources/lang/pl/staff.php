<?php

return [
    'label' => 'Personel',
    'plural_label' => 'Personel',
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
            'label' => 'Hasło',
            'hint' => 'Resetuj hasło',
        ],
        'admin' => [
            'label' => 'Super Admin',
            'helper' => 'Rola super admina nie może być zmieniona w panelu.',
        ],
        'roles' => [
            'label' => 'Role',
            'helper' => ':roles mają pełny dostęp',
        ],
        'permissions' => [
            'label' => 'Uprawnienia',
        ],
        'role' => [
            'label' => 'Rola',
        ],
    ],
    'action' => [
        'acl' => [
            'label' => 'Kontrola dostępu',
        ],
        'add-role' => [
            'label' => 'Dodaj rolę',
        ],
        'delete-role' => [
            'label' => 'Usuń rolę',
            'heading' => 'Usuń rolę: :role',
        ],
    ],
    'acl' => [
        'title' => 'Kontrola dostępu',
        'tooltip' => [
            'roles-included' => 'Uprawnienie jest zawarte w następujących rolach',
        ],
        'notification' => [
            'updated' => 'Zaktualizowano kontrolę dostępu',
            'error' => 'Błąd podczas aktualizacji kontroli dostępu',
            'no-role' => 'Rola nie jest zarejestrowana w Lunar',
            'no-permission' => 'Uprawnienie nie jest zarejestrowane w Lunar',
            'no-role-permission' => 'Rola i uprawnienie nie są zarejestrowane w Lunar',
        ],
    ],
];
