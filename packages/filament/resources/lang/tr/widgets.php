<?php

return [
    'dashboard' => [
        'orders' => [
            'order_stats_overview' => [
                'stat_one' => [
                    'label' => 'Bugünkü siparişler',
                    'increase' => 'Düne göre %:percentage artış (Dün: :count)',
                    'decrease' => 'Düne göre %:percentage azalış (Dün: :count)',
                    'neutral' => 'Düne göre değişiklik yok',
                ],
                'stat_two' => [
                    'label' => 'Son 7 günün siparişleri',
                    'increase' => 'Önceki döneme göre %:percentage artış (Önceki: :count)',
                    'decrease' => 'Önceki döneme göre %:percentage azalış (Önceki: :count)',
                    'neutral' => 'Önceki döneme göre değişiklik yok',
                ],
                'stat_three' => [
                    'label' => 'Son 30 günün siparişleri',
                    'increase' => 'Önceki döneme göre %:percentage artış (Önceki: :count)',
                    'decrease' => 'Önceki döneme göre %:percentage azalış (Önceki: :count)',
                    'neutral' => 'Önceki döneme göre değişiklik yok',
                ],
                'stat_four' => [
                    'label' => 'Bugünkü satışlar',
                    'increase' => 'Düne göre %:percentage artış (Dün: :total)',
                    'decrease' => 'Düne göre %:percentage azalış (Dün: :total)',
                    'neutral' => 'Düne göre değişiklik yok',
                ],
                'stat_five' => [
                    'label' => 'Son 7 günün satışları',
                    'increase' => 'Önceki döneme göre %:percentage artış (Önceki: :total)',
                    'decrease' => 'Önceki döneme göre %:percentage azalış (Önceki: :total)',
                    'neutral' => 'Önceki döneme göre değişiklik yok',
                ],
                'stat_six' => [
                    'label' => 'Son 30 günün satışları',
                    'increase' => 'Önceki döneme göre %:percentage artış (Önceki: :total)',
                    'decrease' => 'Önceki döneme göre %:percentage azalış (Önceki: :total)',
                    'neutral' => 'Önceki döneme göre değişiklik yok',
                ],
            ],
            'order_totals_chart' => [
                'heading' => 'Geçen yılın sipariş toplamları',
                'series_one' => [
                    'label' => 'Bu Dönem',
                ],
                'series_two' => [
                    'label' => 'Önceki Dönem',
                ],
                'yaxis' => [
                    'label' => 'Ciro :currency',
                ],
            ],
            'order_sales_chart' => [
                'heading' => 'Siparişler / Satışlar Raporu',
                'series_one' => [
                    'label' => 'Siparişler',
                ],
                'series_two' => [
                    'label' => 'Gelir',
                ],
                'yaxis' => [
                    'series_one' => [
                        'label' => '# Siparişler',
                    ],
                    'series_two' => [
                        'label' => 'Toplam Değer',
                    ],
                ],
            ],
            'average_order_value' => [
                'heading' => 'Ortalama Sipariş Değeri',
            ],
            'new_returning_customers' => [
                'heading' => 'Yeni vs Geri Dönen Müşteriler',
                'series_one' => [
                    'label' => 'Yeni Müşteriler',
                ],
                'series_two' => [
                    'label' => 'Geri Dönen Müşteriler',
                ],
            ],
            'popular_products' => [
                'heading' => 'En çok satanlar (son 12 ay)',
                'description' => 'Bu rakamlar bir ürünün siparişte kaç kez göründüğüne dayanır, sipariş edilen miktara değil.',
            ],
            'latest_orders' => [
                'heading' => 'Son siparişler',
            ],
        ],
    ],
    'customer' => [
        'stats_overview' => [
            'total_orders' => [
                'label' => 'Toplam siparişler',
            ],
            'avg_spend' => [
                'label' => 'Ort. Harcama',
            ],
            'total_spend' => [
                'label' => 'Toplam Harcama',
            ],
        ],
    ],
    'variant_switcher' => [
        'label' => 'Varyant Değiştir',
        'table' => [
            'sku' => [
                'label' => 'SKU',
            ],
            'values' => [
                'label' => 'Değerler',
            ],
        ],
    ],
];
