<?php

return [

    'label' => 'Vùng Thuế',

    'plural_label' => 'Các Vùng Thuế',

    'table' => [
        'name' => [
            'label' => 'Tên',
        ],
        'zone_type' => [
            'label' => 'Loại Vùng',
        ],
        'active' => [
            'label' => 'Hoạt động',
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
            'label' => 'Loại Vùng',
            'options' => [
                'country' => 'Giới hạn theo Quốc gia',
                'states' => 'Giới hạn theo Tỉnh/Thành phố',
                'postcodes' => 'Giới hạn theo Mã bưu chính',
            ],
        ],
        'price_display' => [
            'label' => 'Hiển thị Giá',
            'options' => [
                'include_tax' => 'Bao gồm Thuế',
                'exclude_tax' => 'Không Bao gồm Thuế',
            ],
        ],
        'active' => [
            'label' => 'Hoạt động',
        ],
        'default' => [
            'label' => 'Mặc định',
        ],

        'zone_countries' => [
            'label' => 'Các Quốc gia',
        ],

        'zone_country' => [
            'label' => 'Quốc gia',
        ],

        'zone_states' => [
            'label' => 'Các Tỉnh/Thành phố',
        ],

        'zone_postcodes' => [
            'label' => 'Các Mã bưu chính',
            'helper' => 'Mỗi mã bưu chính nằm trên một dòng mới. Hỗ trợ ký tự đại diện như NW*',
        ],

    ],

];
