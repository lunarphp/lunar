<?php

return [
    'selection' => [
        'malformed' => 'La sélection du lot est mal formée.',
        'unknown_group' => 'Le lot n\'a pas de groupe d\'options ":id".',
        'unknown_component' => 'L\'option ":id" n\'est pas disponible dans ":group".',
        'duplicate_component' => 'Une option de ":group" a été choisie plusieurs fois.',
        'count' => 'Choisissez entre :min et :max options dans ":group".',
        'unavailable' => 'Le composant du lot ":identifier" n\'est pas disponible en cette quantité.',
    ],
    'nesting' => [
        'is_component' => 'Une variante faisant partie d\'un autre lot ne peut pas être elle-même un lot.',
        'is_bundle' => 'Un lot ne peut pas contenir un autre lot.',
        'self' => 'Un lot ne peut pas contenir sa propre variante.',
    ],
    'definition' => [
        'empty' => 'Un lot doit contenir au moins un composant.',
        'too_many_components' => 'Un lot ne peut pas contenir plus de :max composants.',
        'unknown_group' => 'Le groupe d\'options n\'appartient pas à ce lot.',
        'group_selections' => 'Les limites de sélection de ":group" doivent respecter minimum <= maximum <= nombre d\'options.',
    ],
    'pricing' => [
        'missing_component_price' => 'Le composant ":identifier" n\'a pas de prix en :currency, le lot ne peut donc pas être tarifé dans cette devise.',
    ],
    'console' => [
        'repriced' => ':count lots retarifés.',
    ],
];
