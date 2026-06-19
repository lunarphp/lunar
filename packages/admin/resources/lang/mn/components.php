<?php

return [
    'tags' => [
        'notification' => [
            'updated' => 'Тагнууд шинэчлэгдсэн',
        ],
    ],
    'activity-log' => [
        'input' => [
            'placeholder' => 'Сэтгэгдэл нэмэх',
        ],
        'action' => [
            'add-comment' => 'Сэтгэгдэл нэмэх',
        ],
        'system' => 'Систем',
        'partials' => [
            'orders' => [
                'order_created' => 'Захиалга үүсгэсэн',
                'status_change' => 'Статус шинэчлэгдсэн',
                'order_closed' => 'Захиалга хаагдсан',
                'order_reopened' => 'Захиалга дахин нээгдсэн',
                'order_cancelled' => 'Захиалга цуцлагдсан (:reason)',
                'order_cancelled_no_reason' => 'Захиалга цуцлагдсан',
                'fulfilment_state' => 'Биелүүлэлт #:id-г :state гэж тэмдэглэв',
                'fulfilment_held' => 'Биелүүлэлт #:id түр зогсоогдов (:reason)',
                'fulfilment_held_no_reason' => 'Биелүүлэлт #:id түр зогсоогдов',
                'fulfilment_released' => 'Биелүүлэлт #:id түр зогсолтоос чөлөөлөгдөв',

                'capture' => ':last_four дугаартай картаас :amount төлсөн',
                'authorized' => ':last_four дугаартай картаар :amount баталгаажсан',
                'refund' => ':last_four дугаартай картаас :amount буцаасан',
                'address' => ':type шинэчлэгдсэн',
                'billingAddress' => 'Төлбөрийн хаяг',
                'shippingAddress' => 'Хүргэлтийн хаяг',
            ],
            'update' => [
                'updated' => ':model шинэчлэгдсэн',
            ],
            'create' => [
                'created' => ':model үүсгэсэн',
            ],
            'tags' => [
                'updated' => 'Тагнууд шинэчлэгдсэн',
                'added' => 'Нэмсэн',
                'removed' => 'Устгасан',
            ],
        ],
        'notification' => [
            'comment_added' => 'Сэтгэгдэл нэмэгдсэн',
        ],
    ],
    'forms' => [
        'youtube' => [
            'helperText' => 'YouTube видеоны ID-г оруулна уу. Жишээ нь: dQw4w9WgXcQ',
        ],
    ],
    'collection-tree-view' => [
        'actions' => [
            'move' => [
                'form' => [
                    'target_id' => [
                        'label' => 'Үндсэн коллекц',
                    ],
                ],
            ],
        ],
        'notifications' => [
            'collections-reordered' => [
                'success' => 'Коллекцүүд дахин эрэмбэлэгдсэн',
            ],
            'node-expanded' => [
                'danger' => 'Коллекцүүдийг ачаалах боломжгүй байна',
            ],
            'delete' => [
                'danger' => 'Коллекц устгах боломжгүй байна',
            ],
        ],
    ],
    'product-options-list' => [
        'add-option' => [
            'label' => 'Сонголт нэмэх',
        ],
        'delete-option' => [
            'label' => 'Сонголт устгах',
        ],
        'remove-shared-option' => [
            'label' => 'Хуваалцсан сонголт устгах',
        ],
        'add-value' => [
            'label' => 'Өөр нэг утга нэмэх',
        ],
        'name' => [
            'label' => 'Нэр',
        ],
        'values' => [
            'label' => 'Утгууд',
        ],
    ],
];
