<?php

return [
    'collections' => [
        'create_root' => [
            'label' => 'Créer une collection racine',
        ],
        'create_child' => [
            'label' => 'Créer une collection enfant',
        ],
        'move' => [
            'label' => 'Déplacer la collection',
        ],
        'delete' => [
            'label' => 'Supprimer',
            'notifications' => [
                'cannot_delete' => [
                    'title' => 'Suppression impossible',
                    'body' => 'Cette collection contient des sous-collections et ne peut pas être supprimée.',
                ],
            ],
        ],
    ],
    'orders' => [
        'update_status' => [
            'label' => 'Mettre à jour le statut',
            'wizard' => [
                'step_one' => [
                    'label' => 'Statut',
                ],
                'step_two' => [
                    'label' => 'Emails & Notifications',
                    'no_mailers' => 'Aucun email n’est disponible pour ce statut.',
                ],
                'step_three' => [
                    'label' => 'Aperçu & Enregistrer',
                    'no_mailers' => 'Aucun email n’a été sélectionné pour l’aperçu.',
                ],
            ],
            'notification' => [
                'label' => 'Statut de la commande mis à jour',
            ],
            'billing_email' => [
                'label' => 'Email de facturation',
            ],
            'shipping_email' => [
                'label' => 'Email de livraison',
            ],
        ],

    ],
];
