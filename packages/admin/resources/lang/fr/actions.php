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
            'label' => 'Déplacer une collection',
        ],
        'delete' => [
            'label' => 'Supprimer',
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
                    'label' => 'Mailers & Notifications',
                    'no_mailers' => 'Il n\'y a pas de mailers disponibles pour ce statut.',
                ],
                'step_three' => [
                    'label' => 'Prévisualisation et Enregistrement',
                    'no_mailers' => 'Aucun mailer n\'a été choisi pour la prévisualisation.',
                ],
            ],
            'notification' => [
                'label' => 'Mise à jour de l\'état de commande',
            ],
            'billing_email' => [
                'label' => 'Adresse email facture',
            ],
            'shipping_email' => [
                'label' => 'Adresse email expédition',
            ],
        ],
    ],
];
