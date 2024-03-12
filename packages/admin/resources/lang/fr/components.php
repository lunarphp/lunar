<?php

return [
    'tags' => [
        'notification' => [
            'updated' => 'Mises à jour',
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
                'capture' => 'Paiement de :montant sur la carte finale :last_four',
                'authorized' => 'Autorisation de :montant sur la carte finale :last_four',
                'refund' => 'Remboursement de :montant sur la carte finale :last_four',
                'address' => ':type mis à jour',
                'billingAddress' => 'Adresse facturation',
                'shippingAddress' => 'Adresse expédition',
            ],
            'update' => [
                'updated' => ':model mis à jour',
            ],
            'create' => [
                'created' => ':model créé',
            ],
            'tags' => [
                'updated' => 'Mises à jour des étiquettes',
                'added' => 'Ajoutées',
                'removed' => 'Retirées',
            ],
        ],
        'notification' => [
            'comment_added' => 'Commentaire ajouté',
        ],
    ],
    'forms' => [
        'youtube' => [
            'helperText' => 'Saisir l\'ID du vidéo YouTube. Par exemple, dQw4w9WgXcQ',
        ],
    ],
    'collection-tree-view' => [
        'actions' => [
            'move' => [
                'form' => [
                    'target_id' => [
                        'label' => 'Collection parent',
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
            'label' => 'Retirer l\'option partagée',
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
