<?php

return [
    'tags' => [
        'notification' => [
            'updated' => 'Étiquettes mises à jour',
        ],
    ],

    'activity-log' => [

        'input' => [
            'placeholder' => 'Ajouter un commentaire',
        ],

        'action' => [
            'add-comment' => 'Ajouter un commentaire',
        ],

        'system' => 'Système',

        'partials' => [
            'orders' => [
                'order_created' => 'Commande créée',
                'status_change' => 'Statut mis à jour',
                'capture' => 'Paiement de :amount sur la carte se terminant par :last_four',
                'authorized' => 'Autorisation de :amount sur la carte se terminant par :last_four',
                'refund' => 'Remboursement de :amount sur la carte se terminant par :last_four',
                'address' => ':type mis à jour',
                'billingAddress' => 'Adresse de facturation',
                'shippingAddress' => 'Adresse de livraison',
            ],

            'update' => [
                'updated' => ':model mis à jour',
            ],

            'create' => [
                'created' => ':model créé',
            ],

            'tags' => [
                'updated' => 'Étiquettes mises à jour',
                'added' => 'Ajouté',
                'removed' => 'Supprimé',
            ],
        ],

        'notification' => [
            'comment_added' => 'Commentaire ajouté',
        ],

    ],

    'forms' => [
        'youtube' => [
            'helperText' => 'Entrez l\'ID de la vidéo YouTube. par exemple, dQw4w9WgXcQ',
        ],
    ],

    'collection-tree-view' => [
        'actions' => [
            'move' => [
                'form' => [
                    'target_id' => [
                        'label' => 'Collection parente',
                    ],
                ],
            ],
        ],
        'notifications' => [
            'collections-reordered' => [
                'success' => 'Collections réorganisées',
            ],
            'node-expanded' => [
                'danger' => 'Impossible de charger les collections',
            ],
            'delete' => [
                'danger' => 'Impossible de supprimer la collection',
            ],
        ],
    ],

    'product-options-list' => [
        'add-option' => [
            'label' => 'Ajouter une option',
        ],
        'delete-option' => [
            'label' => 'Supprimer l\'option',
        ],
        'remove-shared-option' => [
            'label' => 'Supprimer l\'option partagée',
        ],
        'add-value' => [
            'label' => 'Ajouter une autre valeur',
        ],
        'name' => [
            'label' => 'Nom',
        ],
        'values' => [
            'label' => 'Valeurs',
        ],
    ],
];
