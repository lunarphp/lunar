<?php

return [
    'label' => 'Equipe',
    'plural_label' => 'Equipe',
    'table' => [
        'prenom' => [
            'label' => 'Prénom',
        ],
        'nom' => [
            'label' => 'Nom de famille',
        ],
        'email' => [
            'label' => 'Email',
        ],
        'admin' => [
            'badge' => 'Super-admin',
        ],
    ],
    'form' => [
        'prenom' => [
            'label' => 'Prénom',
        ],
        'nom' => [
            'label' => 'Nom de famille',
        ],
        'email' => [
            'label' => 'Email',
        ],
        'mot_de_passe' => [
            'label' => 'Mot de passe',
            'hint' => 'Réinitialiser le mot de passe',
        ],
        'admin' => [
            'label' => 'Super-admin',
            'helper' => 'Les rôles super-admin ne peuvent être modifiés dans l\'espace de travail.',
        ],
        'rôles' => [
            'label' => 'Rôles',
            'helper' => ':rôles ont accès illimité',
        ],
        'permissions' => [
            'label' => 'Autorisations',
        ],
        'rôle' => [
            'label' => 'Nom de rôle',
        ],
    ],
    'action' => [
        'acl' => [
            'label' => 'Contrôle d\'accès',
        ],
        'ajouter_rôle' => [
            'label' => 'Ajouter un rôle',
        ],
        'supprimer_rôle' => [
            'label' => 'Supprimer rôle',
            'heading' => 'Supprimer rôle : :rôle',
        ],
    ],
    'acl' => [
        'title' => 'Contrôle d\'accès',
        'tooltip' => [
            'rôles_inclus' => 'Autorisation figurant dans les rôles suivants',
        ],
        'notification' => [
            'mis_à_jour' => 'Mis à jour',
            'erreur' => 'Erreur',
            'pas_de_rôle' => 'Rôle non enregistré sur Lunar',
            'pas_de_permission' => 'Autorisation non enregistrée sur Lunar',
            'pas_de_rôle_permission' => 'Rôle et Autorisation non enregistrées sur Lunar',
        ],
    ],
];
