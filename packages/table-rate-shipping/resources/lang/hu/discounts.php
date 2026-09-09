<?php

return [
    'shipping_discount' => [
        'name' => 'Szállítási ár',
        'form' => [
            'methods' => [
                'label' => 'Szállítási módok',
                'add_label' => 'Szabály hozzáadása',
            ],
            'shipping_method_id' => [
                'label' => 'Szállítási mód',
                'placeholder' => 'Bármely szállítási mód',
            ],
            'type' => [
                'label' => 'Kedvezmény típusa',
                'options' => [
                    'fixed' => 'Fix ár',
                    'percentage' => 'Százalékos kedvezmény',
                ],
            ],
            'percentage' => [
                'label' => 'Százalékos kedvezmény (%)',
            ],
        ],
        'panel' => [
            'rules' => 'Szabályok',
            'rules_description' => 'Minden szabály egy szállítási módra vonatkozik, vagy az összesre, ha nincs kiválasztva egy sem. A konkrét mód elsőbbséget élvez az általános szabállyal szemben.',
            'add_rule' => 'Szabály hozzáadása',
            'remove_rule' => 'Szabály eltávolítása',
            'no_rules' => 'Még nincsenek szabályok. Adjon hozzá egyet a szállítási költség módosításához.',
            'method' => 'Szállítási mód',
            'method_any' => 'Bármely szállítási mód',
            'type' => 'Hatás',
            'type_fixed' => 'Szállítási ár beállítása',
            'type_percentage' => 'Százalékos kedvezmény alkalmazása',
            'percentage' => 'Százalékos kedvezmény (%)',
            'prices' => 'A szállítási ár a következő lesz',
            'prices_hint' => 'A vásárló ezt az összeget fizeti a szállításért. Hagyja üresen a pénznemet, hogy az ára változatlan maradjon; adjon meg 0-t az ingyenes szállításhoz.',
        ],
        'summary_free' => 'Ingyenes szállítás',
        'summary_percentage' => ':percentage% kedvezmény a szállításra',
        'summary_from' => 'Szállítás :amount összegtől',
    ],
];
