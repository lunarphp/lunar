<?php

return [
    'invalid_query' => [
        'title' => 'Буруу хүсэлт',
    ],
    'query' => [
        'malformed_parameter' => ':parameter параметр буруу бүтэцтэй байна.',
        'unknown_include' => ':type дээр танигдахгүй include ":value". Зөвшөөрөгдсөн: :allowed.',
        'include_too_deep' => '":value" include нь :max гэсэн дээд гүнийг хэтэрсэн.',
        'unknown_type' => 'Танигдахгүй нөөцийн төрөл ":value". Зөвшөөрөгдсөн: :allowed.',
        'unknown_field' => ':type дээр танигдахгүй талбар ":value". Зөвшөөрөгдсөн: :allowed.',
        'unknown_filter' => 'Танигдахгүй шүүлтүүр ":value". Зөвшөөрөгдсөн: :allowed.',
        'unknown_operator' => '":filter" шүүлтүүрийн танигдахгүй оператор ":value". Зөвшөөрөгдсөн: :allowed.',
        'unknown_sort' => 'Танигдахгүй эрэмбэлэлт ":value". Зөвшөөрөгдсөн: :allowed.',
        'invalid_page_size' => 'page[size] нь 1-ээс :max хүртэлх бүхэл тоо байх ёстой.',
        'invalid_page_number' => 'page[number] нь эерэг бүхэл тоо байх ёстой.',
        'cursor_unsupported' => ':type нөөц курсор хуудаслалтыг дэмждэггүй.',
        'cursor_and_number' => 'page[cursor] болон page[number]-ийг хамт ашиглах боломжгүй.',
        'invalid_cursor' => 'page[cursor] хүчинтэй курсор биш.',
        'unknown_page_key' => 'Танигдахгүй хуудаслалтын түлхүүр ":value". Зөвшөөрөгдсөн: number, size, cursor.',
    ],
    'resource_not_found' => [
        'title' => 'Олдсонгүй',
        'detail' => '":id" дугаартай :type нөөц байхгүй.',
    ],
    'invalid_header' => [
        'title' => 'Буруу толгой мэдээлэл',
        'detail' => ':header толгойн ":value" утга танигдсангүй.',
    ],
    'invalid_cart_token' => [
        'title' => 'Сагсны токен буруу',
        'detail' => 'X-Lunar-Cart токен буруу эсвэл хугацаа нь дууссан.',
    ],
    'cart_not_found' => [
        'title' => 'Сагс олдсонгүй',
        'detail' => 'X-Lunar-Cart-д заасан сагс байхгүй болсон.',
    ],
    'customer_not_found' => [
        'title' => 'Харилцагч байхгүй',
        'detail' => 'Баталгаажсан хэрэглэгчид харилцагчийн бүртгэл байхгүй.',
    ],
    'validation_failed' => [
        'title' => 'Шалгалт амжилтгүй',
    ],
    'unauthenticated' => [
        'title' => 'Баталгаажаагүй',
        'detail' => 'Хүчинтэй нэвтрэх эрх шаардлагатай.',
    ],
    'forbidden' => [
        'title' => 'Хориглосон',
        'detail' => 'Танд энэ үйлдлийг хийх эрх байхгүй.',
    ],
    'not_found' => [
        'title' => 'Олдсонгүй',
        'detail' => 'Хүссэн хаяг эсвэл нөөц байхгүй.',
    ],
    'method_not_allowed' => [
        'title' => 'Метод зөвшөөрөгдөөгүй',
        'detail' => 'Энэ хаяг тухайн HTTP методыг дэмждэггүй.',
    ],
    'too_many_requests' => [
        'title' => 'Хэт олон хүсэлт',
        'detail' => 'Хүсэлтийн хязгаар хэтэрсэн. Дараа дахин оролдоно уу.',
    ],
    'server_error' => [
        'title' => 'Серверийн алдаа',
        'detail' => 'Ямар нэг зүйл буруу болсон. Дараа дахин оролдоно уу.',
    ],
];
