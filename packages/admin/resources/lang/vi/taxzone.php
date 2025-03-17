<?php

return [

    'label' => 'Vùng thuế',

    'plural_label' => 'Vùng thuế',

    'table' => [
        'name' => [
            'label' => 'Tên',
        ],
        'zone_type' => [
            'label' => 'Loại vùng',
        ],
        'active' => [
            'label' => 'Kích hoạt',
        ],
        'default' => [
            'label' => 'Mặc định',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Tên',
        ],
        'zone_type' => [
            'label' => 'Loại vùng',
            'options' => [
                'country' => 'Giới hạn theo quốc gia',
                'states' => 'Giới hạn theo tỉnh thành',
                'postcodes' => 'Giới hạn theo mã bưu chính',
            ],
        ],
        'price_display' => [
            'label' => 'Hiển thị giá',
            'options' => [
                'include_tax' => 'Bao gồm thuế',
                'exclude_tax' => 'Không bao gồm thuế',
            ],
        ],
        'active' => [
            'label' => 'Kích hoạt',
        ],
        'default' => [
            'label' => 'Mặc định',
        ],

        'zone_countries' => [
            'label' => 'Quốc gia',
        ],

        'zone_country' => [
            'label' => 'Quốc gia',
        ],

        'zone_states' => [
            'label' => 'Tỉnh thành',
        ],

        'zone_postcodes' => [
            'label' => 'Mã bưu chính',
            'helper' => 'Liệt kê mỗi mã bưu chính trên một dòng mới. Hỗ trợ ký tự đại diện như NW*',
        ],

    ],

];
