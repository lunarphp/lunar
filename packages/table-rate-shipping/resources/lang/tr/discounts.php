<?php

return [
    'shipping_discount' => [
        'name' => 'Kargo Ücreti',
        'form' => [
            'methods' => [
                'label' => 'Kargo Yöntemleri',
                'add_label' => 'Kural Ekle',
            ],
            'shipping_method_id' => [
                'label' => 'Kargo Yöntemi',
                'placeholder' => 'Herhangi bir kargo yöntemi',
            ],
            'type' => [
                'label' => 'İndirim Türü',
                'options' => [
                    'fixed' => 'Sabit Ücret',
                    'percentage' => 'Yüzde İndirim',
                ],
            ],
            'percentage' => [
                'label' => 'Yüzde İndirim (%)',
            ],
        ],
        'panel' => [
            'rules' => 'Kurallar',
            'rules_description' => 'Her kural bir kargo yöntemine ya da hiçbiri seçilmediğinde tüm yöntemlere uygulanır. Belirli bir yöntem genel kuralın önüne geçer.',
            'add_rule' => 'Kural ekle',
            'remove_rule' => 'Kuralı kaldır',
            'no_rules' => 'Henüz kural yok. Kargo ücretini değiştirmek için bir kural ekleyin.',
            'method' => 'Kargo yöntemi',
            'method_any' => 'Herhangi bir kargo yöntemi',
            'type' => 'Etki',
            'type_fixed' => 'Kargo ücretini belirle',
            'type_percentage' => 'Yüzde indirim uygula',
            'percentage' => 'Yüzde indirim (%)',
            'prices' => 'Kargo ücreti şu olur',
            'prices_hint' => 'Müşteri kargo için bu tutarı öder. Bir para biriminin fiyatını değiştirmemek için boş bırakın; ücretsiz kargo için 0 girin.',
        ],
        'summary_free' => 'Ücretsiz kargo',
        'summary_percentage' => 'Kargoda %:percentage indirim',
        'summary_from' => ':amount tutarından başlayan kargo',
    ],
];
