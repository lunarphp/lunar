<?php

return [
    'invalid_query' => [
        'title' => 'درخواست نامعتبر',
    ],
    'query' => [
        'malformed_parameter' => 'پارامتر :parameter ساختار نادرستی دارد.',
        'unknown_include' => 'گنجاندن ناشناخته ":value" روی :type. مجاز: :allowed.',
        'include_too_deep' => 'گنجاندن ":value" از حداکثر عمق :max بیشتر است.',
        'unknown_type' => 'نوع منبع ناشناخته ":value". مجاز: :allowed.',
        'unknown_field' => 'فیلد ناشناخته ":value" روی :type. مجاز: :allowed.',
        'unknown_filter' => 'فیلتر ناشناخته ":value". مجاز: :allowed.',
        'unknown_operator' => 'عملگر ناشناخته ":value" برای فیلتر ":filter". مجاز: :allowed.',
        'unknown_sort' => 'مرتب‌سازی ناشناخته ":value". مجاز: :allowed.',
        'invalid_page_size' => 'page[size] باید عددی صحیح بین 1 و :max باشد.',
        'invalid_page_number' => 'page[number] باید عددی صحیح و مثبت باشد.',
        'cursor_unsupported' => 'منبع :type از صفحه‌بندی با مکان‌نما پشتیبانی نمی‌کند.',
        'cursor_and_number' => 'page[cursor] و page[number] را نمی‌توان با هم استفاده کرد.',
        'invalid_cursor' => 'page[cursor] یک مکان‌نمای معتبر نیست.',
        'unknown_page_key' => 'کلید صفحه‌بندی ناشناخته ":value". مجاز: number, size, cursor.',
    ],
    'resource_not_found' => [
        'title' => 'یافت نشد',
        'detail' => 'منبع :type با شناسه ":id" وجود ندارد.',
    ],
    'invalid_header' => [
        'title' => 'هدر نامعتبر',
        'detail' => 'مقدار ":value" برای هدر :header شناخته نشد.',
    ],
    'invalid_cart_token' => [
        'title' => 'توکن سبد خرید نامعتبر',
        'detail' => 'توکن X-Lunar-Cart نامعتبر است یا منقضی شده است.',
    ],
    'cart_not_found' => [
        'title' => 'سبد خرید یافت نشد',
        'detail' => 'سبد خریدی که X-Lunar-Cart به آن اشاره می‌کند دیگر وجود ندارد.',
    ],
    'customer_not_found' => [
        'title' => 'مشتری وجود ندارد',
        'detail' => 'کاربر احراز هویت‌شده رکورد مشتری ندارد.',
    ],
    'validation_failed' => [
        'title' => 'اعتبارسنجی ناموفق بود',
    ],
    'unauthenticated' => [
        'title' => 'احراز هویت نشده',
        'detail' => 'یک اعتبارنامه معتبر لازم است.',
    ],
    'forbidden' => [
        'title' => 'ممنوع',
        'detail' => 'شما اجازه انجام این عمل را ندارید.',
    ],
    'not_found' => [
        'title' => 'یافت نشد',
        'detail' => 'نقطه پایانی یا منبع درخواستی وجود ندارد.',
    ],
    'method_not_allowed' => [
        'title' => 'متد مجاز نیست',
        'detail' => 'این نقطه پایانی از این متد HTTP پشتیبانی نمی‌کند.',
    ],
    'too_many_requests' => [
        'title' => 'درخواست‌های بیش از حد',
        'detail' => 'از محدودیت درخواست‌ها عبور کرده‌اید. بعداً دوباره تلاش کنید.',
    ],
    'server_error' => [
        'title' => 'خطای سرور',
        'detail' => 'مشکلی پیش آمد. بعداً دوباره تلاش کنید.',
    ],
];
