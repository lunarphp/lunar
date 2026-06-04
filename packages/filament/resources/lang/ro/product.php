<?php

return [
    'label' => 'Produs',
    'plural_label' => 'Produse',
    'tabs' => [
        'all' => 'Toate',
        'published' => 'Published',
        'draft' => 'Draft',
    ],
    'status' => [
        'draft' => [
            'content' => 'Currently in draft, this product is hidden from all channels and customer groups.',
        ],
        'archived' => [
            'content' => 'This product is archived — it is hidden from the storefront, but kept on record so historical orders keep their reference. Move it back to Draft to revive it.',
        ],
        'availability' => [
            'customer_groups' => 'Acest produs nu este disponibil momentan pentru niciun grup de clienți.',
            'channels' => 'Acest produs nu este disponibil momentan în niciun canal.',
            'hidden_from_guests' => 'Vizitatorii nu pot vedea sau cumpăra acest produs în acest moment. Grupul de clienți implicit nu este activat sau vizibil pentru acesta.',
            'no_default_customer_group' => 'Niciun grup de clienți implicit nu este setat, așa că vizibilitatea pentru vizitatori nu poate fi controlată aici. Marcați un grup de clienți ca implicit pentru a gestiona accesul vizitatorilor.',
        ],
    ],
    'table' => [
        'status' => [
            'label' => 'Stare',
            'states' => [
                'archived' => 'Archived',
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

        'description' => [
            'label' => 'Description',
        ],

        'short_description' => [
            'label' => 'Short Description',
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
