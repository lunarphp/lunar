<?php

return [

    'label' => 'Mitarbeiter',

    'plural_label' => 'Mitarbeiter',

    'table' => [
        'firstname' => [
            'label' => 'Vorname',
        ],
        'lastname' => [
            'label' => 'Nachname',
        ],
        'email' => [
            'label' => 'E-Mail',
        ],
        'admin' => [
            'badge' => 'Super Admin',
        ],
    ],

    'form' => [
        'firstname' => [
            'label' => 'Vorname',
        ],
        'lastname' => [
            'label' => 'Nachname',
        ],
        'email' => [
            'label' => 'E-Mail',
        ],
        'password' => [
            'label' => 'Passwort',
            'hint' => 'Passwort zurücksetzen',
        ],
        'admin' => [
            'label' => 'Super Admin',
            'helper' => 'Super-Admin-Rollen können im Hub nicht geändert werden.',
        ],
        'roles' => [
            'label' => 'Rollen',
            'helper' => ':roles haben vollen Zugriff',
        ],
        'permissions' => [
            'label' => 'Berechtigungen',
        ],
        'role' => [
            'label' => 'Rollenname',
        ],
    ],

    'action' => [
        'acl' => [
            'label' => 'Zugriffskontrolle',
        ],
        'add-role' => [
            'label' => 'Rolle hinzufügen',
        ],
        'delete-role' => [
            'label' => 'Rolle löschen',
            'heading' => 'Rolle löschen: :role',
        ],
    ],

    'acl' => [
        'title' => 'Zugriffskontrolle',
        'tooltip' => [
            'roles-included' => 'Berechtigung ist in folgenden Rollen enthalten',
        ],
        'notification' => [
            'updated' => 'Aktualisiert',
            'error' => 'Fehler',
            'no-role' => 'Rolle nicht in Lunar registriert',
            'no-permission' => 'Berechtigung nicht in Lunar registriert',
            'no-role-permission' => 'Rolle und Berechtigung nicht in Lunar registriert',
        ],
    ],

];
