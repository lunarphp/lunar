<?php

return [
    'label' => 'Commande',
    'plural_label' => 'Commandes',
    'breadcrumb' => [
        'manage' => 'Gérer',
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
            'label' => 'Order',
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
            'label' => 'Order',
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
            'label' => 'Update Status',
            'notification' => 'Order status updated',
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
            'form' => [
                'tags' => [
                    'label' => 'Tags',
                    'helper_text' => 'Separate tags by pressing Enter, Tab or comma (,)',
                ],
            ],
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

    'fulfilments' => [
        'heading' => 'Fulfilments',
        'unreferenced' => 'Fulfilment #:id',
        'on_hold' => 'On hold',
        'empty' => 'No fulfilments yet.',
        'columns' => [
            'reference' => 'Reference',
            'state' => 'State',
            'items' => 'Items',
            'tracking' => 'Tracking',
            'shipped_at' => 'Shipped at',
            'handed_over' => [
                'shipping' => 'Shipped at',
                'collection' => 'Collected at',
                'digital' => 'Provisioned at',
            ],
            'handed_over_default' => 'Fulfilled at',
        ],
        'actions' => [
            'more' => 'More actions',
            'add_tracking' => [
                'label' => 'Add tracking',
                'modal_heading' => 'Add tracking',
                'notification' => [
                    'success' => 'Tracking added.',
                    'error' => 'Could not add tracking.',
                ],
            ],
            'remove_tracking' => [
                'label' => 'Remove tracking',
                'notification' => [
                    'success' => 'Tracking removed.',
                    'error' => 'Could not remove tracking.',
                ],
            ],
            'create' => [
                'label' => 'Create fulfilment',
                'modal_heading' => 'Create fulfilment',
                'empty' => 'Every line is already fulfilled.',
                'notification' => [
                    'success' => 'Fulfilment created.',
                    'error' => 'Could not create fulfilment.',
                ],
            ],
            'ship' => [
                'label' => 'Mark shipped',
                'modal_heading' => 'Mark fulfilment as shipped',
                'notification' => [
                    'success' => 'Fulfilment marked as shipped.',
                    'error' => 'Could not ship fulfilment.',
                ],
            ],
            'fulfil' => [
                'label' => 'Mark fulfilled',
                'modal_heading' => 'Mark fulfilment as fulfilled',
                'labels' => [
                    'collection' => 'Mark collected',
                ],
                'notification' => [
                    'success' => 'Fulfilment marked as fulfilled.',
                    'error' => 'Could not fulfil fulfilment.',
                ],
            ],
            'cancel' => [
                'label' => 'Cancel fulfilment',
                'modal_heading' => 'Cancel fulfilment',
                'description' => 'This returns the fulfilment to pending so it can be progressed again. Any shipment details are cleared.',
                'notification' => [
                    'success' => 'Fulfilment cancelled.',
                    'error' => 'Could not cancel fulfilment.',
                ],
            ],
            'change_location' => [
                'label' => 'Change location',
                'modal_heading' => 'Change fulfilment location',
                'field' => 'Location',
                'notification' => [
                    'success' => 'Fulfilment location updated.',
                    'error' => 'Could not change the fulfilment location.',
                ],
            ],
            'return' => [
                'label' => 'Return',
                'notification' => [
                    'success' => 'Fulfilment returned.',
                    'error' => 'Could not return fulfilment.',
                ],
            ],
            'update_status' => [
                'label' => 'Update status',
            ],
            'transition' => [
                'modal_heading' => 'Mark fulfilment as :status?',
                'notification' => [
                    'success' => 'Fulfilment status updated.',
                    'error' => 'Could not update the fulfilment status.',
                ],
            ],
            'undo_return' => [
                'label' => 'Undo return',
                'notification' => [
                    'success' => 'Return undone.',
                    'error' => 'Could not undo the return.',
                ],
            ],
            'hold' => [
                'label' => 'Hold fulfilment',
                'modal_heading' => 'Hold fulfilment',
                'reason' => 'Reason',
                'note' => 'Note',
                'notification' => [
                    'success' => 'Fulfilment placed on hold.',
                    'error' => 'Could not hold the fulfilment.',
                ],
            ],
            'release' => [
                'label' => 'Release hold',
                'notification' => [
                    'success' => 'Fulfilment released.',
                    'error' => 'Could not release the fulfilment.',
                ],
            ],
            'split' => [
                'label' => 'Split',
                'confirm' => 'Split fulfilment',
                'cancel' => 'Cancel',
                'empty' => 'Select a quantity to split out.',
                'modal_heading' => 'Split fulfilment',
                'notification' => [
                    'success' => 'Fulfilment split.',
                    'error' => 'Could not split fulfilment.',
                ],
            ],
            'merge' => [
                'label' => 'Merge',
                'confirm' => 'Merge fulfilment',
                'cancel' => 'Cancel',
                'modal_heading' => 'Merge fulfilment',
                'description' => 'Select the items you would like to merge.',
                'target' => 'Merge with',
                'empty' => 'Select items and a destination to merge.',
                'notification' => [
                    'success' => 'Fulfilments merged.',
                    'error' => 'Could not merge fulfilments.',
                ],
            ],
        ],
        'fields' => [
            'quantity' => 'Quantity',
            'tracking' => 'Tracking',
            'tracking_item' => 'Tracking #:number',
            'unit_price' => 'Unit Price',
            'sub_total' => 'Sub Total',
            'discount_total' => 'Discount Total',
            'total' => 'Total',
            'stock_level' => 'Current Stock Level: :count',
            'of' => 'of :count',
            'outstanding' => 'Outstanding: :count',
            'tracking_number' => 'Tracking number',
            'tracking_url' => 'Tracking URL',
            'carrier' => 'Carrier',
            'carrier_custom' => 'Custom / other',
            'tracking_url_help' => 'Only needed for carriers without an automatic tracking link.',
            'shipping_method' => 'Shipping method',
            'move_quantity' => 'Quantity to move out',
        ],
    ],

    'other_items' => [
        'heading' => 'Other items',
    ],
];
