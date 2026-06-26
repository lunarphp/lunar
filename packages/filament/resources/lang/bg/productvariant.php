<?php

return [
    'inventory' => [
        'summary_heading' => 'Stock',
        'location' => 'Location: :location',
        'on_hand' => 'On hand',
        'available' => 'Available',
        'committed' => 'Committed',
        'reserved' => 'Reserved',
        'recent_movements' => 'Recent movements',
        'no_movements' => 'No stock movements yet.',
    ],
    'label' => 'Продуктов вариант',
    'plural_label' => 'Продуктови варианти',
    'pages' => [
        'edit' => [
            'title' => 'Основна информация',
        ],
        'media' => [
            'title' => 'Медия',
            'form' => [
                'no_selection' => [
                    'label' => 'В момента няма избрано изображение за този вариант.',
                ],
                'no_media_available' => [
                    'label' => 'Няма налична медия за този продукт.',
                ],
                'images' => [
                    'label' => 'Основно изображение',
                    'helper_text' => 'Изберете изображение на продукта, което представя този вариант.',
                ],
            ],
        ],
        'identifiers' => [
            'title' => 'Идентификатори',
        ],
        'inventory' => [
            'title' => 'Наличности',
        ],
        'shipping' => [
            'title' => 'Доставка',
        ],
    ],
    'form' => [
        'sku' => [
            'label' => 'SKU',
        ],
        'gtin' => [
            'label' => 'Глобален търговски номер (GTIN)',
        ],
        'mpn' => [
            'label' => 'Номер на част от производителя (MPN)',
        ],
        'ean' => [
            'label' => 'UPC/EAN',
        ],
        'backorder' => [
            'tooltip' => 'How many units you will accept orders for beyond stock on hand. Used only when Selling Policy allows backorders.',
            'label' => 'По заявка',
        ],
        'purchasable' => [
            'tooltip' => 'When this variant can be purchased. In Stock sells only while units are available; In Stock or On Backorder also sells the backorder allowance; Always ignores stock entirely.',
            'label' => 'Selling Policy',
            'options' => [
                'always' => 'Винаги',
                'in_stock' => 'В наличност',
                'in_stock_or_on_backorder' => 'В наличност или по заявка',
            ],
        ],
        'unit_quantity' => [
            'label' => 'Количество в единица',
            'tooltip' => 'Колко отделни артикула съставляват 1 единица.',
        ],
        'min_quantity' => [
            'label' => 'Минимално количество',
            'tooltip' => 'Минималното количество от варианта, което може да бъде закупено в една поръчка.',
        ],
        'quantity_increment' => [
            'label' => 'Стъпка на количество',
            'tooltip' => 'Вариантът трябва да бъде закупуван в кратни на това количество.',
        ],
        'tax_class_id' => [
            'label' => 'Данъчен клас',
        ],
        'shippable' => [
            'label' => 'Подлежащ на доставка',
        ],
        'length_value' => [
            'label' => 'Дължина',
        ],
        'length_unit' => [
            'label' => 'Единица за дължина',
        ],
        'width_value' => [
            'label' => 'Ширина',
        ],
        'width_unit' => [
            'label' => 'Единица за ширина',
        ],
        'height_value' => [
            'label' => 'Височина',
        ],
        'height_unit' => [
            'label' => 'Единица за височина',
        ],
        'weight_value' => [
            'label' => 'Тегло',
        ],
        'weight_unit' => [
            'label' => 'Единица за тегло',
        ],
    ],
];
