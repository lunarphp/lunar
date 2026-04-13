<?php

return [
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
        'stock' => [
            'label' => 'В наличност',
        ],
        'backorder' => [
            'label' => 'По заявка',
        ],
        'purchasable' => [
            'label' => 'Възможност за закупуване',
            'options' => [
                'always' => 'Винаги',
                'in_stock' => 'В наличност',
                'in_stock_or_on_backorder' => 'В наличност или по заявка',
            ],
        ],
        'unit_quantity' => [
            'label' => 'Количество в единица',
            'helper_text' => 'Колко отделни артикула съставляват 1 единица.',
        ],
        'min_quantity' => [
            'label' => 'Минимално количество',
            'helper_text' => 'Минималното количество от варианта, което може да бъде закупено в една поръчка.',
        ],
        'quantity_increment' => [
            'label' => 'Стъпка на количество',
            'helper_text' => 'Вариантът трябва да бъде закупуван в кратни на това количество.',
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
