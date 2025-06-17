<?php

return [

    'label' => 'Option de produit',

    'plural_label' => 'Options de produit',

    'table' => [
        'name' => [
            'label' => 'Nom',
        ],
        'label' => [
            'label' => 'Étiquette',
        ],
        'handle' => [
            'label' => 'Identifiant',
        ],
        'shared' => [
            'label' => 'Partagé',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nom',
        ],
        'label' => [
            'label' => 'Étiquette',
        ],
        'handle' => [
            'label' => 'Identifiant',
        ],
    ],

    'widgets' => [
        'product-options' => [
            'notifications' => [
                'save-variants' => [
                    'success' => [
                        'title' => 'Variantes de produit enregistrées',
                    ],
                ],
            ],
            'actions' => [
                'cancel' => [
                    'label' => 'Annuler',
                ],
                'save-options' => [
                    'label' => 'Enregistrer les options',
                ],
                'add-shared-option' => [
                    'label' => 'Ajouter une option partagée',
                    'form' => [
                        'product_option' => [
                            'label' => 'Option de produit',
                        ],
                        'no_shared_components' => [
                            'label' => 'Aucune option partagée n\'est disponible.',
                        ],
                    ],
                ],
                'add-restricted-option' => [
                    'label' => 'Ajouter une option',
                ],
            ],
            'options-list' => [
                'empty' => [
                    'heading' => 'Aucune option de produit configurée',
                    'description' => 'Ajoutez une option de produit partagée ou restreinte pour commencer à générer des variantes.',
                ],
            ],
            'options-table' => [
                'title' => 'Options de produit',
                'configure-options' => [
                    'label' => 'Configurer les options',
                ],
                'table' => [
                    'option' => [
                        'label' => 'Option',
                    ],
                    'values' => [
                        'label' => 'Valeurs',
                    ],
                ],
            ],
            'variants-table' => [
                'title' => 'Variantes de produit',
                'actions' => [
                    'create' => [
                        'label' => 'Créer une variante',
                    ],
                    'edit' => [
                        'label' => 'Modifier',
                    ],
                    'delete' => [
                        'label' => 'Supprimer',
                    ],
                ],
                'empty' => [
                    'heading' => 'Aucune variante configurée',
                ],
                'table' => [
                    'new' => [
                        'label' => 'NOUVEAU',
                    ],
                    'option' => [
                        'label' => 'Option',
                    ],
                    'sku' => [
                        'label' => 'SKU',
                    ],
                    'price' => [
                        'label' => 'Prix',
                    ],
                    'stock' => [
                        'label' => 'Stock',
                    ],
                ],
            ],
        ],
    ],

];
