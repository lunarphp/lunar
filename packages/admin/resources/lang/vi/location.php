<?php

return [
    'label' => 'Địa điểm',

    'plural_label' => 'Địa điểm',

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Không thể xóa địa điểm này vì đang có đơn giao hàng được gán cho nó.',
            ],
        ],
    ],
];
