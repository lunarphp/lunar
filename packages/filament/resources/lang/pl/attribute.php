<?php

return [
    'label' => 'Atrybut',
    'plural_label' => 'Atrybuty',
    'table' => [
        'name' => [
            'label' => 'Nazwa',
        ],
        'description' => [
            'label' => 'Opis',
        ],
        'handle' => [
            'label' => 'Slug',
        ],
        'type' => [
            'label' => 'Typ',
        ],
    ],
    'form' => [
        'model_types' => [
            'label' => 'Applies to',
            'product_and_variant_invalid' => 'An attribute cannot apply to both Product and Product Variant.',
        ],
        'attributable_type' => [
            'label' => 'Typ',
        ],
        'name' => [
            'label' => 'Nazwa',
        ],
        'description' => [
            'label' => 'Opis',
            'helper' => 'Użyj, aby wyświetlić podpowiedź poniżej wpisu',
        ],
        'handle' => [
            'label' => 'Slug',
        ],
        'searchable' => [
            'label' => 'Możliwość wyszukiwania',
        ],
        'filterable' => [
            'label' => 'Możliwość filtrowania',
        ],
        'required' => [
            'label' => 'Wymagane',
        ],
        'type' => [
            'label' => 'Typ',
        ],
        'validation_rules' => [
            'label' => 'Reguły walidacji',
            'helper' => 'Jedna reguła na wpis, na przykład: min:1, max:10',
        ],
    ],
];
