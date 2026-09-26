<?php

return [
    'invalid_query' => [
        'title' => 'Requête invalide',
    ],
    'query' => [
        'malformed_parameter' => 'Le paramètre :parameter est mal formé.',
        'unknown_include' => 'Inclusion inconnue ":value" sur :type. Autorisées : :allowed.',
        'include_too_deep' => 'L\'inclusion ":value" dépasse la profondeur maximale de :max.',
        'unknown_type' => 'Type de ressource inconnu ":value". Autorisés : :allowed.',
        'unknown_field' => 'Champ inconnu ":value" sur :type. Autorisés : :allowed.',
        'unknown_filter' => 'Filtre inconnu ":value". Autorisés : :allowed.',
        'unknown_operator' => 'Opérateur inconnu ":value" pour le filtre ":filter". Autorisés : :allowed.',
        'unknown_sort' => 'Tri inconnu ":value". Autorisés : :allowed.',
        'invalid_page_size' => 'page[size] doit être un nombre entier compris entre 1 et :max.',
        'invalid_page_number' => 'page[number] doit être un nombre entier positif.',
        'cursor_unsupported' => 'La ressource :type ne prend pas en charge la pagination par curseur.',
        'cursor_and_number' => 'page[cursor] et page[number] ne peuvent pas être combinés.',
        'invalid_cursor' => 'page[cursor] n\'est pas un curseur valide.',
        'unknown_page_key' => 'Clé de pagination inconnue ":value". Autorisées : number, size, cursor.',
    ],
    'resource_not_found' => [
        'title' => 'Introuvable',
        'detail' => 'Aucune ressource :type avec l\'identifiant ":id" n\'existe.',
    ],
    'invalid_header' => [
        'title' => 'En-tête invalide',
        'detail' => 'La valeur ":value" de l\'en-tête :header n\'est pas reconnue.',
    ],
    'invalid_cart_token' => [
        'title' => 'Jeton de panier invalide',
        'detail' => 'Le jeton X-Lunar-Cart est invalide ou a expiré.',
    ],
    'cart_not_found' => [
        'title' => 'Panier introuvable',
        'detail' => 'Le panier référencé par X-Lunar-Cart n\'existe plus.',
    ],
    'customer_not_found' => [
        'title' => 'Aucun client',
        'detail' => 'L\'utilisateur authentifié n\'a pas de fiche client.',
    ],
    'validation_failed' => [
        'title' => 'Échec de la validation',
    ],
    'unauthenticated' => [
        'title' => 'Non authentifié',
        'detail' => 'Un identifiant valide est requis.',
    ],
    'forbidden' => [
        'title' => 'Interdit',
        'detail' => 'Vous n\'avez pas la permission d\'effectuer cette action.',
    ],
    'not_found' => [
        'title' => 'Introuvable',
        'detail' => 'Le point de terminaison ou la ressource demandée n\'existe pas.',
    ],
    'method_not_allowed' => [
        'title' => 'Méthode non autorisée',
        'detail' => 'Ce point de terminaison ne prend pas en charge cette méthode HTTP.',
    ],
    'too_many_requests' => [
        'title' => 'Trop de requêtes',
        'detail' => 'La limite de requêtes a été dépassée. Réessayez plus tard.',
    ],
    'server_error' => [
        'title' => 'Erreur serveur',
        'detail' => 'Une erreur s\'est produite. Réessayez plus tard.',
    ],
];
