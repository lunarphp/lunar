<?php

return [
    'collections' => [
        'create_root' => [
            'label' => 'Үндсэн коллекц үүсгэх',
        ],
        'create_child' => [
            'label' => 'Дэд коллекц үүсгэх',
        ],
        'move' => [
            'label' => 'Коллекц шилжүүлэх',
        ],
        'delete' => [
            'label' => 'Устгах',
            'notifications' => [
                'cannot_delete' => [
                    'title' => 'Устгах боломжгүй',
                    'body' => 'Энэ коллекц дэд коллекцүүдтэй тул устгах боломжгүй байна.',
                ],
            ],
        ],
    ],
    'orders' => [
        'update_status' => [
            'label' => 'Статус шинэчлэх',
            'wizard' => [
                'step_one' => [
                    'label' => 'Статус',
                ],
                'step_two' => [
                    'label' => 'Мэйлэрүүд болон мэдэгдлүүд',
                    'no_mailers' => 'Энэ статусд мэйлэр байхгүй байна.',
                ],
                'step_three' => [
                    'label' => 'Урьдчилан үзэх болон хадгалах',
                    'no_mailers' => 'Урьдчилан үзэх мэйлэр сонгогдоогүй байна.',
                ],
            ],
            'notification' => [
                'label' => 'Захиалгын статус шинэчлэгдсэн',
            ],
            'billing_email' => [
                'label' => 'Төлбөрийн имэйл',
            ],
            'shipping_email' => [
                'label' => 'Хүргэлтийн имэйл',
            ],
        ],

    ],
];
