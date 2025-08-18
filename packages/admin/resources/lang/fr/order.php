<?php

return [

    'label' => 'Commande',

    'plural_label' => 'Commandes',

    'breadcrumb' => [
        'manage' => 'Gérer',
    ],

    'tabs' => [
        'all' => 'Toutes',
    ],

    'transactions' => [
        'capture' => 'Capturé',
        'intent' => 'Intention',
        'refund' => 'Remboursé',
        'failed' => 'Échoué',
    ],

    'table' => [
        'status' => [
            'label' => 'Statut',
        ],
        'reference' => [
            'label' => 'Référence',
        ],
        'customer_reference' => [
            'label' => 'Référence client',
        ],
        'customer' => [
            'label' => 'Client',
        ],
        'tags' => [
            'label' => 'Étiquettes',
        ],
        'postcode' => [
            'label' => 'Code postal',
        ],
        'email' => [
            'label' => 'Email',
            'copy_message' => 'Adresse email copiée',
        ],
        'phone' => [
            'label' => 'Téléphone',
        ],
        'total' => [
            'label' => 'Total',
        ],
        'date' => [
            'label' => 'Date',
        ],
        'new_customer' => [
            'label' => 'Type de client',
        ],
        'placed_after' => [
            'label' => 'Placée après',
        ],
        'placed_before' => [
            'label' => 'Placée avant',
        ],
    ],

    'form' => [
        'address' => [
            'first_name' => [
                'label' => 'Prénom',
            ],
            'last_name' => [
                'label' => 'Nom de famille',
            ],
            'line_one' => [
                'label' => 'Adresse ligne 1',
            ],
            'line_two' => [
                'label' => 'Adresse ligne 2',
            ],
            'line_three' => [
                'label' => 'Adresse ligne 3',
            ],
            'company_name' => [
                'label' => 'Nom de l\'entreprise',
            ],
            'tax_identifier' => [
                'label' => 'Identifiant fiscal',
            ],
            'contact_phone' => [
                'label' => 'Téléphone',
            ],
            'contact_email' => [
                'label' => 'Adresse email',
            ],
            'city' => [
                'label' => 'Ville',
            ],
            'state' => [
                'label' => 'État / Province',
            ],
            'postcode' => [
                'label' => 'Code postal',
            ],
            'country_id' => [
                'label' => 'Pays',
            ],
        ],

        'reference' => [
            'label' => 'Référence',
        ],
        'status' => [
            'label' => 'Statut',
        ],
        'transaction' => [
            'label' => 'Transaction',
        ],
        'amount' => [
            'label' => 'Montant',

            'hint' => [
                'less_than_total' => 'Vous êtes sur le point de capturer un montant inférieur à la valeur totale de la transaction',
            ],
        ],

        'notes' => [
            'label' => 'Notes',
        ],
        'confirm' => [
            'label' => 'Confirmer',

            'alert' => 'Confirmation requise',

            'hint' => [
                'capture' => 'Veuillez confirmer que vous souhaitez capturer ce paiement',
                'refund' => 'Veuillez confirmer que vous souhaitez rembourser ce montant.',
            ],
        ],
    ],

    'infolist' => [
        'notes' => [
            'label' => 'Notes',
            'placeholder' => 'Pas de notes sur cette commande',
        ],
        'delivery_instructions' => [
            'label' => 'Instructions de livraison',
        ],
        'shipping_total' => [
            'label' => 'Total livraison',
        ],
        'paid' => [
            'label' => 'Payé',
        ],
        'refund' => [
            'label' => 'Remboursement',
        ],
        'unit_price' => [
            'label' => 'Prix unitaire',
        ],
        'quantity' => [
            'label' => 'Quantité',
        ],
        'sub_total' => [
            'label' => 'Sous-total',
        ],
        'discount_total' => [
            'label' => 'Total réduction',
        ],
        'total' => [
            'label' => 'Total',
        ],
        'current_stock_level' => [
            'message' => 'Niveau de stock actuel : :count',
        ],
        'purchase_stock_level' => [
            'message' => 'au moment de la commande : :count',
        ],
        'status' => [
            'label' => 'Statut',
        ],
        'reference' => [
            'label' => 'Référence',
        ],
        'customer_reference' => [
            'label' => 'Référence client',
        ],
        'channel' => [
            'label' => 'Canal',
        ],
        'date_created' => [
            'label' => 'Date de création',
        ],
        'date_placed' => [
            'label' => 'Date de placement',
        ],
        'new_returning' => [
            'label' => 'Nouveau / Récurrent',
        ],
        'new_customer' => [
            'label' => 'Nouveau client',
        ],
        'returning_customer' => [
            'label' => 'Client récurrent',
        ],
        'shipping_address' => [
            'label' => 'Adresse de livraison',
        ],
        'billing_address' => [
            'label' => 'Adresse de facturation',
        ],
        'address_not_set' => [
            'label' => 'Pas d\'adresse définie',
        ],
        'billing_matches_shipping' => [
            'label' => 'Identique à l\'adresse de livraison',
        ],
        'additional_info' => [
            'label' => 'Informations supplémentaires',
        ],
        'no_additional_info' => [
            'label' => 'Pas d\'informations supplémentaires',
        ],
        'tags' => [
            'label' => 'Étiquettes',
        ],
        'timeline' => [
            'label' => 'Chronologie',
        ],
        'transactions' => [
            'label' => 'Transactions',
            'placeholder' => 'Aucune transaction',
        ],
        'alert' => [
            'requires_capture' => 'Cette commande nécessite toujours que le paiement soit capturé.',
            'partially_refunded' => 'Cette commande a été partiellement remboursée.',
            'refunded' => 'Cette commande a été remboursée.',
        ],
    ],

    'action' => [
        'bulk_update_status' => [
            'label' => 'Mettre à jour le statut',
            'notification' => 'Statut des commandes mis à jour',
        ],
        'update_status' => [
            'new_status' => [
                'label' => 'Nouveau statut',
            ],
            'additional_content' => [
                'label' => 'Contenu supplémentaire',
            ],
            'additional_email_recipient' => [
                'label' => 'Destinataire email supplémentaire',
                'placeholder' => 'facultatif',
            ],
        ],
        'download_order_pdf' => [
            'label' => 'Télécharger le PDF',
            'notification' => 'Téléchargement du PDF de commande',
        ],
        'edit_address' => [
            'label' => 'Modifier',

            'notification' => [
                'error' => 'Erreur',

                'billing_address' => [
                    'saved' => 'Adresse de facturation enregistrée',
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
            'label' => 'Capturer le paiement',

            'notification' => [
                'error' => 'Il y a eu un problème avec la capture',
                'success' => 'Capture réussie',
            ],
        ],
        'refund_payment' => [
            'label' => 'Rembourser',

            'notification' => [
                'error' => 'Il y a eu un problème avec le remboursement',
                'success' => 'Remboursement réussi',
            ],
        ],
    ],

];
