<?php

return [
    'shipping_discount' => [
        'name' => 'Хүргэлтийн үнэ',
        'form' => [
            'methods' => [
                'label' => 'Хүргэлтийн арга',
                'add_label' => 'Дүрэм нэмэх',
            ],
            'shipping_method_id' => [
                'label' => 'Хүргэлтийн арга',
                'placeholder' => 'Аливаа хүргэлтийн арга',
            ],
            'type' => [
                'label' => 'Хөнгөлөлтийн төрөл',
                'options' => [
                    'fixed' => 'Тогтсон үнэ',
                    'percentage' => 'Хувиар хөнгөлөх',
                ],
            ],
            'percentage' => [
                'label' => 'Хөнгөлөх хувь (%)',
            ],
        ],
    ],
];
