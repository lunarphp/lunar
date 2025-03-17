<?php

return [
    'label' => 'Khu vực vận chuyển',
    'label_plural' => 'Khu vực vận chuyển',
    'form' => [
        'unrestricted' => [
            'content' => 'Khu vực vận chuyển này không có hạn chế và sẽ khả dụng cho tất cả khách hàng khi thanh toán.',
        ],
        'name' => [
            'label' => 'Tên',
        ],
        'type' => [
            'label' => 'Loại',
            'options' => [
                'unrestricted' => 'Không giới hạn',
                'countries' => 'Giới hạn theo quốc gia',
                'states' => 'Giới hạn theo tỉnh/thành',
                'postcodes' => 'Giới hạn theo mã bưu chính',
            ],
        ],
        'country' => [
            'label' => 'Quốc gia',
        ],
        'states' => [
            'label' => 'Tỉnh/thành',
        ],
        'countries' => [
            'label' => 'Tỉnh/thành',
        ],
        'postcodes' => [
            'label' => 'Mã bưu chính',
            'helper' => 'Liệt kê mỗi mã bưu chính trên một dòng mới. Hỗ trợ ký tự đại diện như NW*',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Tên',
        ],
        'type' => [
            'label' => 'Loại',
            'options' => [
                'unrestricted' => 'Không giới hạn',
                'countries' => 'Giới hạn theo quốc gia',
                'states' => 'Giới hạn theo tỉnh/thành',
                'postcodes' => 'Giới hạn theo mã bưu chính',
            ],
        ],
    ],
];
