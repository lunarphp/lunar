<?php

return [

    'label' => 'Захиалга',

    'plural_label' => 'Захиалгууд',

    'breadcrumb' => [
        'manage' => 'Удирдах',
    ],

    'tabs' => [
        'all' => 'Бүгд',
    ],

    'transactions' => [
        'capture' => 'Төлсөн',
        'intent' => 'Нөөц',
        'refund' => 'Буцаасан',
        'failed' => 'Амжилтгүй',
    ],

    'table' => [
        'status' => [
            'label' => 'Статус',
        ],
        'reference' => [
            'label' => 'Лавлагаа',
        ],
        'customer_reference' => [
            'label' => 'Харилцагчийн лавлагаа',
        ],
        'customer' => [
            'label' => 'Харилцагч',
        ],
        'tags' => [
            'label' => 'Таг',
        ],
        'postcode' => [
            'label' => 'Шуудангийн код',
        ],
        'email' => [
            'label' => 'Имэйл',
            'copy_message' => 'Имэйл хаяг хуулагдсан',
        ],
        'phone' => [
            'label' => 'Утас',
        ],
        'total' => [
            'label' => 'Нийт',
        ],
        'date' => [
            'label' => 'Он сар өдөр',
        ],
        'new_customer' => [
            'label' => 'Харилцагчийн төрөл',
        ],
        'placed_after' => [
            'label' => 'Дараа захиалсан',
        ],
        'placed_before' => [
            'label' => 'Өмнө захиалсан',
        ],
    ],

    'form' => [
        'address' => [
            'first_name' => [
                'label' => 'Нэр',
            ],
            'last_name' => [
                'label' => 'Овог',
            ],
            'line_one' => [
                'label' => 'Хаяг мөр 1',
            ],
            'line_two' => [
                'label' => 'Хаяг мөр 2',
            ],
            'line_three' => [
                'label' => 'Хаяг мөр 3',
            ],
            'company_name' => [
                'label' => 'Компаний нэр',
            ],
            'tax_identifier' => [
                'label' => 'Татварын таниулагч',
            ],
            'contact_phone' => [
                'label' => 'Утас',
            ],
            'contact_email' => [
                'label' => 'Имэйл хаяг',
            ],
            'city' => [
                'label' => 'Хот',
            ],
            'state' => [
                'label' => 'Аймаг/Муж',
            ],
            'postcode' => [
                'label' => 'Шуудангийн код',
            ],
            'country_id' => [
                'label' => 'Улс',
            ],
        ],

        'reference' => [
            'label' => 'Лавлагаа',
        ],
        'status' => [
            'label' => 'Статус',
        ],
        'transaction' => [
            'label' => 'Гүйлгээ',
        ],
        'amount' => [
            'label' => 'Дүн',
            'hint' => [
                'less_than_total' => 'Та нийт гүйлгээнээс бага дүн төлөх гэж байна',
            ],
        ],

        'notes' => [
            'label' => 'Тэмдэглэл',
        ],
        'confirm' => [
            'label' => 'Баталгаажуулах',
            'alert' => 'Баталгаа шаардлагатай',
            'hint' => [
                'capture' => 'Энэ төлбөрийг баталгаажуулахыг хүсэж буйгаа батална уу',
                'refund' => 'Энэ дүнг буцаахыг баталгаажуулна уу.',
            ],
        ],
    ],

    'infolist' => [
        'notes' => [
            'label' => 'Тэмдэглэл',
            'placeholder' => 'Энэ захиалгад тэмдэглэл байхгүй',
        ],
        'delivery_instructions' => [
            'label' => 'Хүргэлтийн заавар',
        ],
        'shipping_total' => [
            'label' => 'Хүргэлтийн нийт',
        ],
        'paid' => [
            'label' => 'Төлсөн',
        ],
        'refund' => [
            'label' => 'Буцаах',
        ],
        'unit_price' => [
            'label' => 'Нэгж үнэ',
        ],
        'quantity' => [
            'label' => 'Тоо хэмжээ',
        ],
        'sub_total' => [
            'label' => 'Дэд нийт',
        ],
        'discount_total' => [
            'label' => 'Хөнгөлөлтийн нийт',
        ],
        'total' => [
            'label' => 'Нийт',
        ],
        'current_stock_level' => [
            'message' => 'Одоогийн агуулахын нөөц: :count',
        ],
        'purchase_stock_level' => [
            'message' => 'захиалга өгсөн үед: :count',
        ],
        'status' => [
            'label' => 'Статус',
        ],
        'reference' => [
            'label' => 'Лавлагаа',
        ],
        'customer_reference' => [
            'label' => 'Харилцагчийн лавлагаа',
        ],
        'channel' => [
            'label' => 'Суваг',
        ],
        'date_created' => [
            'label' => 'Үүсгэсэн огноо',
        ],
        'date_placed' => [
            'label' => 'Захиалсан огноо',
        ],
        'new_returning' => [
            'label' => 'Шинэ / Дахин ирсэн',
        ],
        'new_customer' => [
            'label' => 'Шинэ харилцагч',
        ],
        'returning_customer' => [
            'label' => 'Дахин ирсэн харилцагч',
        ],
        'shipping_address' => [
            'label' => 'Хүргэлтийн хаяг',
        ],
        'billing_address' => [
            'label' => 'Төлбөрийн хаяг',
        ],
        'address_not_set' => [
            'label' => 'Хаяг тохируулаагүй',
        ],
        'billing_matches_shipping' => [
            'label' => 'Хүргэлтийн хаягтай адил',
        ],
        'additional_info' => [
            'label' => 'Нэмэлт мэдээлэл',
        ],
        'no_additional_info' => [
            'label' => 'Нэмэлт мэдээлэл байхгүй',
        ],
        'tags' => [
            'label' => 'Таг',
        ],
        'timeline' => [
            'label' => 'Цаг хугацаа',
        ],
        'transactions' => [
            'label' => 'Гүйлгээнүүд',
            'placeholder' => 'Гүйлгээ байхгүй',
        ],
        'alert' => [
            'requires_capture' => 'Энэ захиалгын төлбөрийг баталгаажуулах шаардлагатай байна.',
            'partially_refunded' => 'Энэ захиалгад хэсэгчлэн буцаах үйлдэл хийгдсэн.',
            'refunded' => 'Энэ захиалга буцаагдсан.',
        ],
    ],

    'action' => [
        'bulk_update_status' => [
            'label' => 'Статус шинэчлэх',
            'notification' => 'Захиалгын статус шинэчлэгдсэн',
        ],
        'update_status' => [
            'label' => 'Статус шинэчлэх',
            'notification' => 'Захиалгын статус шинэчлэгдсэн',
            'new_status' => [
                'label' => 'Шинэ статус',
            ],
            'additional_content' => [
                'label' => 'Нэмэлт контент',
            ],
            'additional_email_recipient' => [
                'label' => 'Нэмэлт имэйл хүлээн авагч',
                'placeholder' => 'сонголттой',
            ],
        ],
        'download_order_pdf' => [
            'label' => 'PDF татах',
            'notification' => 'Захиалгын PDF татаж байна',
        ],
        'edit_address' => [
            'label' => 'Засах',
            'notification' => [
                'error' => 'Алдаа',
                'billing_address' => [
                    'saved' => 'Төлбөрийн хаяг хадгалагдсан',
                ],
                'shipping_address' => [
                    'saved' => 'Хүргэлтийн хаяг хадгалагдсан',
                ],
            ],
        ],
        'edit_tags' => [
            'label' => 'Засах',
            'form' => [
                'tags' => [
                    'label' => 'Таг',
                    'helper_text' => 'Тагыг Enter, Tab эсвэл таслал (,) дарж тусгаарлана уу',
                ],
            ],
        ],
        'capture_payment' => [
            'label' => 'Төлбөр баталгаажуулах',
            'notification' => [
                'error' => 'Төлбөр баталгаажуулахад асуудал гарлаа',
                'success' => 'Төлбөр амжилттай баталгаажлаа',
            ],
        ],
        'refund_payment' => [
            'label' => 'Буцаах',
            'notification' => [
                'error' => 'Буцаах явцад асуудал гарсан',
                'success' => 'Амжилттай буцаасан',
            ],
        ],
    ],

];
