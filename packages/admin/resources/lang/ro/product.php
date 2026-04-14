<?php

return [

    'label' => 'Produs',

    'plural_label' => 'Produse',

    'tabs' => [
        'all' => 'Toate',
    ],

    'status' => [
        'unpublished' => [
            'content' => 'În prezent în stadiu de ciornă, acest produs este ascuns în toate canalele și grupurile de clienți.',
        ],
        'availability' => [
            'customer_groups' => 'Acest produs nu este disponibil momentan pentru niciun grup de clienți.',
            'channels' => 'Acest produs nu este disponibil momentan în niciun canal.',
        ],
    ],

    'table' => [
        'status' => [
            'label' => 'Stare',
            'states' => [
                'deleted' => 'Șters',
                'draft' => 'Ciornă',
                'published' => 'Publicat',
            ],
        ],
        'name' => [
            'label' => 'Nume',
        ],
        'brand' => [
            'label' => 'Marcă',
        ],
        'sku' => [
            'label' => 'Cod stoc intern (SKU)',
        ],
        'stock' => [
            'label' => 'Stoc',
        ],
        'producttype' => [
            'label' => 'Tip produs',
        ],
    ],

    'actions' => [
        'edit_status' => [
            'label' => 'Actualizează starea',
            'heading' => 'Actualizează starea',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nume',
        ],
        'brand' => [
            'label' => 'Marcă',
        ],
        'sku' => [
            'label' => 'Cod stoc intern (SKU)',
        ],
        'producttype' => [
            'label' => 'Tip produs',
        ],
        'status' => [
            'label' => 'Stare',
            'options' => [
                'published' => [
                    'label' => 'Publicat',
                    'description' => 'Acest produs va fi disponibil în toate grupurile de clienți și canalele activate',
                ],
                'draft' => [
                    'label' => 'Ciornă',
                    'description' => 'Acest produs va fi ascuns în toate canalele și grupurile de clienți',
                ],
            ],
        ],
        'tags' => [
            'label' => 'Etichete',
            'helper_text' => 'Separați etichetele apăsând Enter, Tab sau virgulă (,)',
        ],
        'collections' => [
            'label' => 'Colecții',
            'select_collection' => 'Selectează o colecție',
        ],
    ],

    'pages' => [
        'availability' => [
            'label' => 'Disponibilitate',
        ],
        'edit' => [
            'title' => 'Informații de bază',
        ],
        'identifiers' => [
            'label' => 'Identificatori produs',
        ],
        'inventory' => [
            'label' => 'Stoc',
        ],
        'pricing' => [
            'form' => [
                'tax_class_id' => [
                    'label' => 'Clasă de taxe',
                ],
                'tax_ref' => [
                    'label' => 'Referință taxe',
                    'helper_text' => 'Opțional, pentru integrare cu sisteme terțe.',
                ],
            ],
        ],
        'shipping' => [
            'label' => 'Livrare',
        ],
        'variants' => [
            'label' => 'Variante',
        ],
        'collections' => [
            'label' => 'Colecții',
            'select_collection' => 'Selectează o colecție',
        ],
        'associations' => [
            'label' => 'Asocieri produs',
        ],
    ],

];
