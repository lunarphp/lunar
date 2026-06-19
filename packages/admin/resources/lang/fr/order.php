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
        'heading' => 'Traitements',
        'unreferenced' => 'Traitement #:id',
        'on_hold' => 'En attente',
        'empty' => 'Aucun traitement pour le moment.',
        'columns' => [
            'reference' => 'Référence',
            'state' => 'État',
            'items' => 'Articles',
            'tracking' => 'Suivi',
            'shipped_at' => 'Expédié le',
            'handed_over' => [
                'shipping' => 'Expédié le',
                'collection' => 'Retiré le',
                'digital' => 'Mis à disposition le',
            ],
            'handed_over_default' => 'Traité le',
        ],
        'actions' => [
            'more' => 'Plus d\'actions',
            'notify' => 'Notifier le client',
            'add_tracking' => [
                'label' => 'Ajouter un suivi',
                'modal_heading' => 'Ajouter un suivi',
                'notification' => [
                    'success' => 'Suivi ajouté.',
                    'error' => 'Impossible d\'ajouter le suivi.',
                ],
            ],
            'remove_tracking' => [
                'label' => 'Supprimer le suivi',
                'notification' => [
                    'success' => 'Suivi supprimé.',
                    'error' => 'Impossible de supprimer le suivi.',
                ],
            ],
            'create' => [
                'label' => 'Créer un traitement',
                'modal_heading' => 'Créer un traitement',
                'empty' => 'Chaque ligne est déjà traitée.',
                'notification' => [
                    'success' => 'Traitement créé.',
                    'error' => 'Impossible de créer le traitement.',
                ],
            ],
            'ship' => [
                'label' => 'Marquer comme expédié',
                'modal_heading' => 'Marquer le traitement comme expédié',
                'notification' => [
                    'success' => 'Traitement marqué comme expédié.',
                    'error' => 'Impossible d\'expédier le traitement.',
                ],
            ],
            'fulfil' => [
                'label' => 'Marquer comme traité',
                'modal_heading' => 'Marquer le traitement comme traité',
                'labels' => [
                    'collection' => 'Marquer comme retiré',
                ],
                'notification' => [
                    'success' => 'Traitement marqué comme traité.',
                    'error' => 'Impossible de traiter le traitement.',
                ],
            ],
            'cancel' => [
                'label' => 'Annuler le traitement',
                'modal_heading' => 'Annuler le traitement',
                'description' => 'Cela rétablit le traitement à l\'état en attente afin qu\'il puisse être repris. Les détails d\'expédition sont effacés.',
                'notification' => [
                    'success' => 'Traitement annulé.',
                    'error' => 'Impossible d\'annuler le traitement.',
                ],
            ],
            'change_location' => [
                'label' => 'Changer d\'emplacement',
                'modal_heading' => 'Changer l\'emplacement du traitement',
                'field' => 'Emplacement',
                'notification' => [
                    'success' => 'Emplacement du traitement mis à jour.',
                    'error' => 'Impossible de changer l\'emplacement du traitement.',
                ],
            ],
            'return' => [
                'label' => 'Retourner',
                'notification' => [
                    'success' => 'Traitement retourné.',
                    'error' => 'Impossible de retourner le traitement.',
                ],
            ],
            'update_status' => [
                'label' => 'Mettre à jour le statut',
            ],
            'transition' => [
                'modal_heading' => 'Marquer le traitement comme :status ?',
                'notification' => [
                    'success' => 'Statut du traitement mis à jour.',
                    'error' => 'Impossible de mettre à jour le statut du traitement.',
                ],
            ],
            'undo_return' => [
                'label' => 'Annuler le retour',
                'notification' => [
                    'success' => 'Retour annulé.',
                    'error' => 'Impossible d\'annuler le retour.',
                ],
            ],
            'hold' => [
                'label' => 'Mettre en attente le traitement',
                'modal_heading' => 'Mettre en attente le traitement',
                'reason' => 'Motif',
                'note' => 'Note',
                'notification' => [
                    'success' => 'Traitement mis en attente.',
                    'error' => 'Impossible de mettre en attente le traitement.',
                ],
            ],
            'release' => [
                'label' => 'Lever l\'attente',
                'notification' => [
                    'success' => 'Traitement sorti de l\'attente.',
                    'error' => 'Impossible de sortir le traitement de l\'attente.',
                ],
            ],
            'split' => [
                'label' => 'Diviser',
                'confirm' => 'Diviser le traitement',
                'cancel' => 'Annuler',
                'empty' => 'Sélectionnez une quantité à extraire.',
                'modal_heading' => 'Diviser le traitement',
                'notification' => [
                    'success' => 'Traitement divisé.',
                    'error' => 'Impossible de diviser le traitement.',
                ],
            ],
            'merge' => [
                'label' => 'Fusionner',
                'confirm' => 'Fusionner le traitement',
                'cancel' => 'Annuler',
                'modal_heading' => 'Fusionner le traitement',
                'description' => 'Sélectionnez les articles que vous souhaitez fusionner.',
                'target' => 'Fusionner avec',
                'empty' => 'Sélectionnez des articles et une destination pour fusionner.',
                'notification' => [
                    'success' => 'Traitements fusionnés.',
                    'error' => 'Impossible de fusionner les traitements.',
                ],
            ],
        ],
        'fields' => [
            'quantity' => 'Quantité',
            'tracking' => 'Suivi',
            'tracking_item' => 'Suivi #:number',
            'unit_price' => 'Prix unitaire',
            'sub_total' => 'Sous-total',
            'discount_total' => 'Total des remises',
            'total' => 'Total',
            'stock_level' => 'Niveau de stock actuel : :count',
            'of' => 'sur :count',
            'outstanding' => 'Restant : :count',
            'tracking_number' => 'Numéro de suivi',
            'tracking_url' => 'URL de suivi',
            'carrier' => 'Transporteur',
            'carrier_custom' => 'Personnalisé / autre',
            'tracking_url_help' => 'Nécessaire uniquement pour les transporteurs sans lien de suivi automatique.',
            'shipping_method' => 'Méthode de livraison',
            'move_quantity' => 'Quantité à déplacer',
        ],
    ],

    'other_items' => [
        'heading' => 'Autres articles',
    ],
];
