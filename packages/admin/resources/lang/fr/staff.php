<?php

return [

    'label' => 'Personnel',

    'plural_label' => 'Personnel',

    'table' => [
        'first_name' => [
            'label' => 'Prénom',
        ],
        'last_name' => [
            'label' => 'Nom de famille',
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
            'label' => 'Prénom',
        ],
        'last_name' => [
            'label' => 'Nom de famille',
        ],
        'email' => [
            'label' => 'Email',
        ],
        'password' => [
            'label' => 'Mot de passe',
            'hint' => 'Réinitialiser le mot de passe',
        ],
        'admin' => [
            'label' => 'Super Admin',
            'helper' => 'Les rôles de super admin ne peuvent pas être modifiés dans le hub.',
        ],
        'roles' => [
            'label' => 'Rôles',
            'helper' => ':roles ont un accès complet',
        ],
        'permissions' => [
            'label' => 'Autorisations',
        ],
        'role' => [
            'label' => 'Nom du rôle',
        ],
    ],

    'action' => [
        'acl' => [
            'label' => 'Contrôle d\'accès',
        ],
        'add-role' => [
            'label' => 'Ajouter un rôle',
        ],
        'delete-role' => [
            'label' => 'Supprimer un rôle',
            'heading' => 'Supprimer le rôle : :role',
        ],
    ],

    'acl' => [
        'title' => 'Contrôle d\'accès',
        'tooltip' => [
            'roles-included' => 'L\'autorisation est incluse dans les rôles suivants',
        ],
        'notification' => [
            'updated' => 'Mis à jour',
            'error' => 'Erreur',
            'no-role' => 'Rôle non enregistré dans Lunar',
            'no-permission' => 'Autorisation non enregistrée dans Lunar',
            'no-role-permission' => 'Rôle et autorisation non enregistrés dans Lunar',
        ],
    ],

];
