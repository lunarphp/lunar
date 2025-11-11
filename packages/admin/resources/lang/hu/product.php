<?php

return [

    'label' => 'Termék',

    'plural_label' => 'Termékek',

    'tabs' => [
        'all' => 'Mind',
    ],

    'status' => [
        'unpublished' => [
            'content' => 'Jelenleg vázlat státuszban van, ez a termék minden csatornán és vásárlói csoportban rejtve van.',
        ],
        'availability' => [
            'customer_groups' => 'Ez a termék jelenleg nem elérhető egyik vásárlói csoport számára sem.',
            'channels' => 'Ez a termék jelenleg nem elérhető egyik csatornán sem.',
        ],
    ],

    'table' => [
        'status' => [
            'label' => 'Státusz',
            'states' => [
                'deleted' => 'Törölve',
                'draft' => 'Vázlat',
                'published' => 'Közzétéve',
            ],
        ],
        'name' => [
            'label' => 'Név',
        ],
        'brand' => [
            'label' => 'Márka',
        ],
        'sku' => [
            'label' => 'Cikkszám (SKU)',
        ],
        'stock' => [
            'label' => 'Készlet',
        ],
        'producttype' => [
            'label' => 'Terméktípus',
        ],
    ],

    'actions' => [
        'edit_status' => [
            'label' => 'Státusz frissítése',
            'heading' => 'Státusz frissítése',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Név',
        ],
        'brand' => [
            'label' => 'Márka',
        ],
        'sku' => [
            'label' => 'Cikkszám (SKU)',
        ],
        'producttype' => [
            'label' => 'Terméktípus',
        ],
        'status' => [
            'label' => 'Státusz',
            'options' => [
                'published' => [
                    'label' => 'Közzétéve',
                    'description' => 'Ez a termék elérhető lesz minden engedélyezett vásárlói csoportban és csatornán',
                ],
                'draft' => [
                    'label' => 'Vázlat',
                    'description' => 'Ez a termék rejtett lesz minden csatornán és vásárlói csoportban',
                ],
            ],
        ],
        'tags' => [
            'label' => 'Címkék',
            'helper_text' => 'Címkék elválasztása Enterrel, Tab-bal vagy vesszővel (,)',
        ],
        'collections' => [
            'label' => 'Gyűjtemények',
            'select_collection' => 'Válassz gyűjteményt',
        ],
    ],

    'pages' => [
        'availability' => [
            'label' => 'Elérhetőség',
        ],
        'edit' => [
            'title' => 'Alapvető információk',
        ],
        'identifiers' => [
            'label' => 'Termékazonosítók',
        ],
        'inventory' => [
            'label' => 'Készlet',
        ],
        'pricing' => [
            'form' => [
                'tax_class_id' => [
                    'label' => 'Adóosztály',
                ],
                'tax_ref' => [
                    'label' => 'Adóreferencia',
                    'helper_text' => 'Opcionális, külső rendszerekkel való integrációhoz.',
                ],
            ],
        ],
        'shipping' => [
            'label' => 'Szállítás',
        ],
        'variants' => [
            'label' => 'Változatok',
        ],
        'collections' => [
            'label' => 'Gyűjtemények',
            'select_collection' => 'Válassz gyűjteményt',
        ],
        'associations' => [
            'label' => 'Termékasszociációk',
        ],
    ],

];
