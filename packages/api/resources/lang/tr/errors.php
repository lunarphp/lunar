<?php

return [
    'invalid_query' => [
        'title' => 'Geçersiz sorgu',
    ],
    'query' => [
        'malformed_parameter' => ':parameter parametresi hatalı biçimlendirilmiş.',
        'unknown_include' => ':type üzerinde bilinmeyen dahil etme ":value". İzin verilenler: :allowed.',
        'include_too_deep' => '":value" dahil etmesi en fazla :max olan derinliği aşıyor.',
        'unknown_type' => 'Bilinmeyen kaynak türü ":value". İzin verilenler: :allowed.',
        'unknown_field' => ':type üzerinde bilinmeyen alan ":value". İzin verilenler: :allowed.',
        'unknown_filter' => 'Bilinmeyen filtre ":value". İzin verilenler: :allowed.',
        'unknown_operator' => '":filter" filtresi için bilinmeyen operatör ":value". İzin verilenler: :allowed.',
        'unknown_sort' => 'Bilinmeyen sıralama ":value". İzin verilenler: :allowed.',
        'invalid_page_size' => 'page[size] 1 ile :max arasında bir tam sayı olmalıdır.',
        'invalid_page_number' => 'page[number] pozitif bir tam sayı olmalıdır.',
        'cursor_unsupported' => ':type kaynağı imleç sayfalamayı desteklemiyor.',
        'cursor_and_number' => 'page[cursor] ve page[number] birlikte kullanılamaz.',
        'invalid_cursor' => 'page[cursor] geçerli bir imleç değil.',
        'unknown_page_key' => 'Bilinmeyen sayfalama anahtarı ":value". İzin verilenler: number, size, cursor.',
    ],
    'resource_not_found' => [
        'title' => 'Bulunamadı',
        'detail' => '":id" kimliğine sahip bir :type kaynağı yok.',
    ],
    'invalid_header' => [
        'title' => 'Geçersiz başlık',
        'detail' => ':header başlığı için ":value" değeri tanınmıyor.',
    ],
    'invalid_cart_token' => [
        'title' => 'Geçersiz sepet belirteci',
        'detail' => 'X-Lunar-Cart belirteci geçersiz veya süresi dolmuş.',
    ],
    'cart_not_found' => [
        'title' => 'Sepet bulunamadı',
        'detail' => 'X-Lunar-Cart ile belirtilen sepet artık mevcut değil.',
    ],
    'customer_not_found' => [
        'title' => 'Müşteri yok',
        'detail' => 'Kimliği doğrulanmış kullanıcının müşteri kaydı yok.',
    ],
    'validation_failed' => [
        'title' => 'Doğrulama başarısız',
    ],
    'unauthenticated' => [
        'title' => 'Kimlik doğrulanmadı',
        'detail' => 'Geçerli bir kimlik bilgisi gerekli.',
    ],
    'forbidden' => [
        'title' => 'Yasak',
        'detail' => 'Bu işlemi gerçekleştirme izniniz yok.',
    ],
    'not_found' => [
        'title' => 'Bulunamadı',
        'detail' => 'İstenen uç nokta veya kaynak mevcut değil.',
    ],
    'method_not_allowed' => [
        'title' => 'Yönteme izin verilmiyor',
        'detail' => 'Bu uç nokta bu HTTP yöntemini desteklemiyor.',
    ],
    'too_many_requests' => [
        'title' => 'Çok fazla istek',
        'detail' => 'İstek sınırı aşıldı. Daha sonra tekrar deneyin.',
    ],
    'server_error' => [
        'title' => 'Sunucu hatası',
        'detail' => 'Bir şeyler yanlış gitti. Daha sonra tekrar deneyin.',
    ],
];
