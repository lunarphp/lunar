<?php

return [
    'invalid_query' => [
        'title' => 'استعلام غير صالح',
    ],
    'query' => [
        'malformed_parameter' => 'المعامل :parameter غير صحيح التكوين.',
        'unknown_include' => 'تضمين غير معروف ":value" على :type. المسموح: :allowed.',
        'include_too_deep' => 'التضمين ":value" يتجاوز الحد الأقصى للعمق :max.',
        'unknown_type' => 'نوع مورد غير معروف ":value". المسموح: :allowed.',
        'unknown_field' => 'حقل غير معروف ":value" على :type. المسموح: :allowed.',
        'unknown_filter' => 'مرشّح غير معروف ":value". المسموح: :allowed.',
        'unknown_operator' => 'عامل غير معروف ":value" للمرشّح ":filter". المسموح: :allowed.',
        'unknown_sort' => 'ترتيب غير معروف ":value". المسموح: :allowed.',
        'invalid_page_size' => 'يجب أن يكون page[size] عددًا صحيحًا بين 1 و :max.',
        'invalid_page_number' => 'يجب أن يكون page[number] عددًا صحيحًا موجبًا.',
        'cursor_unsupported' => 'المورد :type لا يدعم التصفح بالمؤشر.',
        'cursor_and_number' => 'لا يمكن استخدام page[cursor] و page[number] معًا.',
        'invalid_cursor' => 'page[cursor] ليس مؤشرًا صالحًا.',
        'unknown_page_key' => 'مفتاح تصفح غير معروف ":value". المسموح: number, size, cursor.',
    ],
    'resource_not_found' => [
        'title' => 'غير موجود',
        'detail' => 'لا يوجد مورد :type بالمعرف ":id".',
    ],
    'invalid_header' => [
        'title' => 'ترويسة غير صالحة',
        'detail' => 'القيمة ":value" للترويسة :header غير معروفة.',
    ],
    'invalid_cart_token' => [
        'title' => 'رمز سلة غير صالح',
        'detail' => 'رمز X-Lunar-Cart غير صالح أو منتهي الصلاحية.',
    ],
    'cart_not_found' => [
        'title' => 'السلة غير موجودة',
        'detail' => 'السلة المشار إليها في X-Lunar-Cart لم تعد موجودة.',
    ],
    'customer_not_found' => [
        'title' => 'لا يوجد عميل',
        'detail' => 'المستخدم المصادق عليه ليس لديه سجل عميل.',
    ],
    'validation_failed' => [
        'title' => 'فشل التحقق',
    ],
    'unauthenticated' => [
        'title' => 'غير مصادق',
        'detail' => 'يلزم بيان اعتماد صالح.',
    ],
    'forbidden' => [
        'title' => 'ممنوع',
        'detail' => 'ليست لديك صلاحية لتنفيذ هذا الإجراء.',
    ],
    'not_found' => [
        'title' => 'غير موجود',
        'detail' => 'نقطة النهاية أو المورد المطلوب غير موجود.',
    ],
    'method_not_allowed' => [
        'title' => 'الطريقة غير مسموح بها',
        'detail' => 'نقطة النهاية هذه لا تدعم طريقة HTTP المطلوبة.',
    ],
    'too_many_requests' => [
        'title' => 'طلبات كثيرة جدًا',
        'detail' => 'تم تجاوز حد الطلبات. أعد المحاولة لاحقًا.',
    ],
    'server_error' => [
        'title' => 'خطأ في الخادم',
        'detail' => 'حدث خطأ ما. أعد المحاولة لاحقًا.',
    ],
];
