<?php

return [
    'invalid_query' => [
        'title' => 'Невалидна заявка',
    ],
    'query' => [
        'malformed_parameter' => 'Параметърът :parameter е неправилно форматиран.',
        'unknown_include' => 'Неизвестно включване ":value" за :type. Разрешени: :allowed.',
        'include_too_deep' => 'Включването ":value" надвишава максималната дълбочина от :max.',
        'unknown_type' => 'Неизвестен тип ресурс ":value". Разрешени: :allowed.',
        'unknown_field' => 'Неизвестно поле ":value" за :type. Разрешени: :allowed.',
        'unknown_filter' => 'Неизвестен филтър ":value". Разрешени: :allowed.',
        'unknown_operator' => 'Неизвестен оператор ":value" за филтър ":filter". Разрешени: :allowed.',
        'unknown_sort' => 'Неизвестно сортиране ":value". Разрешени: :allowed.',
        'invalid_page_size' => 'page[size] трябва да е цяло число между 1 и :max.',
        'invalid_page_number' => 'page[number] трябва да е положително цяло число.',
        'cursor_unsupported' => 'Ресурсът :type не поддържа страниране с курсор.',
        'cursor_and_number' => 'page[cursor] и page[number] не могат да се комбинират.',
        'invalid_cursor' => 'page[cursor] не е валиден курсор.',
        'unknown_page_key' => 'Неизвестен ключ за страниране ":value". Разрешени: number, size, cursor.',
    ],
    'resource_not_found' => [
        'title' => 'Не е намерено',
        'detail' => 'Не съществува ресурс :type с идентификатор ":id".',
    ],
    'invalid_header' => [
        'title' => 'Невалиден хедър',
        'detail' => 'Стойността ":value" за хедъра :header не е разпозната.',
    ],
    'invalid_cart_token' => [
        'title' => 'Невалиден токен на кошница',
        'detail' => 'Токенът X-Lunar-Cart е невалиден или изтекъл.',
    ],
    'cart_not_found' => [
        'title' => 'Кошницата не е намерена',
        'detail' => 'Кошницата, посочена в X-Lunar-Cart, вече не съществува.',
    ],
    'customer_not_found' => [
        'title' => 'Няма клиент',
        'detail' => 'Удостовереният потребител няма клиентски запис.',
    ],
    'validation_failed' => [
        'title' => 'Валидацията е неуспешна',
    ],
    'unauthenticated' => [
        'title' => 'Неудостоверен',
        'detail' => 'Изисква се валидно удостоверение.',
    ],
    'forbidden' => [
        'title' => 'Забранено',
        'detail' => 'Нямате разрешение да извършите това действие.',
    ],
    'not_found' => [
        'title' => 'Не е намерено',
        'detail' => 'Заявеният адрес или ресурс не съществува.',
    ],
    'method_not_allowed' => [
        'title' => 'Методът не е разрешен',
        'detail' => 'Този адрес не поддържа този HTTP метод.',
    ],
    'too_many_requests' => [
        'title' => 'Твърде много заявки',
        'detail' => 'Лимитът на заявките е превишен. Опитайте отново по-късно.',
    ],
    'server_error' => [
        'title' => 'Сървърна грешка',
        'detail' => 'Нещо се обърка. Опитайте отново по-късно.',
    ],
];
