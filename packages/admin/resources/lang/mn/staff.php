<?php

return [

    'label' => 'Ажилтан',

    'plural_label' => 'Ажилчид',

    'table' => [
        'first_name' => [
            'label' => 'Нэр',
        ],
        'last_name' => [
            'label' => 'Овог',
        ],
        'email' => [
            'label' => 'Имэйл',
        ],
        'admin' => [
            'badge' => 'Супер Админ',
        ],
    ],

    'form' => [
        'first_name' => [
            'label' => 'Нэр',
        ],
        'last_name' => [
            'label' => 'Овог',
        ],
        'email' => [
            'label' => 'Имэйл',
        ],
        'password' => [
            'label' => 'Нууц үг',
            'hint' => 'Нууц үг сэргээх',
        ],
        'admin' => [
            'label' => 'Супер Админ',
            'helper' => 'Супер админы дүрүүд hub дээр өөрчлөгдөх боломжгүй.',
        ],
        'roles' => [
            'label' => 'Дүрүүд',
            'helper' => ':roles бүрэн эрхтэй',
        ],
        'permissions' => [
            'label' => 'Эрхүүд',
        ],
        'role' => [
            'label' => 'Дүрийн нэр',
        ],
    ],

    'action' => [
        'acl' => [
            'label' => 'Хандалтын хяналт',
        ],
        'add-role' => [
            'label' => 'Дүр нэмэх',
        ],
        'delete-role' => [
            'label' => 'Дүр устгах',
            'heading' => 'Дүр устгах: :role',
        ],
    ],

    'acl' => [
        'title' => 'Хандалтын хяналт',
        'tooltip' => [
            'roles-included' => 'Эрх дараах дүрүүдэд багтсан',
        ],
        'notification' => [
            'updated' => 'Шинэчлэгдсэн',
            'error' => 'Алдаа',
            'no-role' => 'Дүр Lunar дээр бүртгэгдээгүй байна',
            'no-permission' => 'Эрх Lunar дээр бүртгэгдээгүй байна',
            'no-role-permission' => 'Дүр болон Эрх Lunar дээр бүртгэгдээгүй байна',
        ],
    ],

];
