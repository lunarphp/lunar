<?php

return [
    'shipping_discount' => [
        'name' => 'Prix de l\'expédition',
        'form' => [
            'methods' => [
                'label' => 'Méthodes d\'expédition',
                'add_label' => 'Ajouter une règle',
            ],
            'shipping_method_id' => [
                'label' => 'Méthode d\'expédition',
                'placeholder' => 'Toute méthode d\'expédition',
            ],
            'type' => [
                'label' => 'Type de remise',
                'options' => [
                    'fixed' => 'Prix fixe',
                    'percentage' => 'Remise en pourcentage',
                ],
            ],
            'percentage' => [
                'label' => 'Remise en pourcentage (%)',
            ],
        ],
        'panel' => [
            'rules' => 'Règles',
            'rules_description' => 'Chaque règle s\'applique à une méthode d\'expédition, ou à toutes les méthodes si aucune n\'est choisie. Une méthode spécifique prime sur la règle générale.',
            'add_rule' => 'Ajouter une règle',
            'remove_rule' => 'Supprimer la règle',
            'no_rules' => 'Aucune règle pour le moment. Ajoutez-en une pour modifier le coût de l\'expédition.',
            'method' => 'Méthode d\'expédition',
            'method_any' => 'Toute méthode d\'expédition',
            'type' => 'Effet',
            'type_fixed' => 'Définir le prix de l\'expédition',
            'type_percentage' => 'Appliquer une remise en pourcentage',
            'percentage' => 'Remise en pourcentage (%)',
            'prices' => 'Le prix de l\'expédition devient',
            'prices_hint' => 'Le client paie ce montant pour l\'expédition. Laissez une devise vide pour conserver son prix inchangé ; saisissez 0 pour une expédition gratuite.',
        ],
        'summary_free' => 'Expédition gratuite',
        'summary_percentage' => ':percentage% de remise sur l\'expédition',
        'summary_from' => 'Expédition à partir de :amount',
    ],
];
