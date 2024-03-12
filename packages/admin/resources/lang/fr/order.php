<?php

return [
    'label' => 'Commande',
    'plural_label' => 'Commandes',
    'breadcrumb' => [
        'manage' => 'Gérer',
    ],
    'transactions' => [
        'traitees' => 'Traitées',
        'en_attente' => 'En attente',
    ],
    'informations' => [
        'label' => 'Informations',
        'additional_info' => [
            'label' => 'Informations supplémentaires',
        ],
    ],
    'client' => [
        'label' => 'Client',
        'new_customer' => [
            'label' => 'Nouveau Client',
        ],
        'returning_customer' => [
            'label' => 'Client Retournant',
        ],
    ],
    'timeline' => [
        'label' => 'Calendrier',
        'transactions' => [
            'label' => 'Transactions',
            'placeholder' => 'Aucune transaction',
        ],
    ],
    'action' => [
        'bulk_update_status' => [
            'label' => 'Mettre à jour le statut',
            'notification' => 'Statut de commandes mis à jour',
        ],
        'update_status' => [
            'new_status' => [
                'label' => 'Nouveau statut',
            ],
            'additional_content' => [
                'label' => 'Contenu supplémentaire',
            ],
            'additional_email_recipient' => [
                'label' => 'Destinataire supplémentaire d\'email',
                'placeholder' => 'Facultatif',
            ],
        ],
        'download_order_pdf' => [
            'label' => 'Télécharger le PDF',
            'notification' => 'PDF de la commande en téléchargement',
        ],
        'edit_address' => [
            'label' => 'Modifier',
            'notification' => [
                'error' => 'Erreur',
                'billing_address' => [
                    'saved' => 'Adresse facturation enregistrée',
                ],
                'shipping_address' => [
                    'saved' => 'Adresse de livraison enregistrée',
                ],
            ],
        ],
        'edit_tags' => [
            'label' => 'Modifier',
        ],
        'capture_payment' => [
            'label' => 'Capter le paiement',
            'notification' => [
                'error' => 'Problème avec le capture',
                'success' => 'Capture réussie',
            ],
        ],
        'refund_payment' => [
            'label' => 'Rembourser',
            'notification' => [
                'error' => 'Problème avec le remboursement',
                'success' => 'Remboursement réussi',
            ],
        ],
    ],
    'stock' => [
        'current_stock_level' => [
            'message' => 'Niveau de stock actuel : :count',
        ],
        'purchase_stock_level' => [
            'message' => 'à la date de commande :count',
        ],
    ],
    'commande' => [
        'label' => 'Commande',
        'reference' => [
            'label' => 'Référence',
        ],
        'client_reference' => [
            'label' => 'Référence client',
        ],
        'channel' => [
            'label' => 'Canal',
        ],
        'date_created' => [
            'label' => 'Date de création',
        ],
        'date_placed' => [
            'label' => 'Date passée',
        ],
        'new_returning' => [
            'label' => 'Nouvel / Retournant',
        ],
    ],
    'alert' => [
        'requires_capture' => 'Cette commande nécessite encore un paiement à capturer.',
        'partially_refunded' => 'Cette commande a été partiellement remboursée.',
        'refunded' => 'Cette commande a été remboursée.',
    ],
];
