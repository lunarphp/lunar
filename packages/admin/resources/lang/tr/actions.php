<?php

return [
    'collections' => [
        'create_root' => [
            'label' => 'Ana Koleksiyon Oluştur',
        ],
        'create_child' => [
            'label' => 'Alt Koleksiyon Oluştur',
        ],
        'move' => [
            'label' => 'Koleksiyonu Taşı',
        ],
        'delete' => [
            'label' => 'Sil',
            'notifications' => [
                'cannot_delete' => [
                    'title' => 'Silinemiyor',
                    'body' => 'Bu koleksiyonun alt koleksiyonları var ve silinemez.',
                ],
            ],
        ],
    ],
    'orders' => [
        'update_status' => [
            'label' => 'Durumu Güncelle',
            'wizard' => [
                'step_one' => [
                    'label' => 'Durum',
                ],
                'step_two' => [
                    'label' => 'E-posta & Bildirimler',
                    'no_mailers' => 'Bu durum için kullanılabilir e-posta şablonu yok.',
                ],
                'step_three' => [
                    'label' => 'Önizleme & Kaydet',
                    'no_mailers' => 'Önizleme için seçilmiş e-posta şablonu yok.',
                ],
            ],
            'notification' => [
                'label' => 'Sipariş durumu güncellendi',
            ],
            'billing_email' => [
                'label' => 'Fatura E-postası',
            ],
            'shipping_email' => [
                'label' => 'Kargo E-postası',
            ],
        ],

    ],
];
