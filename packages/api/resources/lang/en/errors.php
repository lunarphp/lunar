<?php

return [
    'invalid_query' => [
        'title' => 'Invalid query',
    ],
    'query' => [
        'malformed_parameter' => 'The :parameter parameter is malformed.',
        'unknown_include' => 'Unknown include ":value" on :type. Allowed: :allowed.',
        'include_too_deep' => 'Include ":value" exceeds the maximum depth of :max.',
        'unknown_type' => 'Unknown resource type ":value". Allowed: :allowed.',
        'unknown_field' => 'Unknown field ":value" on :type. Allowed: :allowed.',
        'unknown_filter' => 'Unknown filter ":value". Allowed: :allowed.',
        'unknown_operator' => 'Unknown operator ":value" for filter ":filter". Allowed: :allowed.',
        'unknown_sort' => 'Unknown sort ":value". Allowed: :allowed.',
        'invalid_page_size' => 'page[size] must be a whole number between 1 and :max.',
        'invalid_page_number' => 'page[number] must be a positive whole number.',
        'cursor_unsupported' => 'The :type resource does not support cursor pagination.',
        'cursor_and_number' => 'page[cursor] and page[number] cannot be combined.',
        'invalid_cursor' => 'page[cursor] is not a valid cursor.',
        'unknown_page_key' => 'Unknown pagination key ":value". Allowed: number, size, cursor.',
    ],
    'resource_not_found' => [
        'title' => 'Not found',
        'detail' => 'No :type resource with id ":id" exists.',
    ],
    'invalid_header' => [
        'title' => 'Invalid header',
        'detail' => 'The value ":value" for the :header header is not recognised.',
    ],
    'invalid_cart_token' => [
        'title' => 'Invalid cart token',
        'detail' => 'The X-Lunar-Cart token is invalid or has expired.',
    ],
    'cart_not_found' => [
        'title' => 'Cart not found',
        'detail' => 'The cart referenced by X-Lunar-Cart no longer exists.',
    ],
    'customer_not_found' => [
        'title' => 'No customer',
        'detail' => 'The authenticated user has no customer record.',
    ],
    'validation_failed' => [
        'title' => 'Validation failed',
    ],
    'unauthenticated' => [
        'title' => 'Unauthenticated',
        'detail' => 'A valid credential is required.',
    ],
    'forbidden' => [
        'title' => 'Forbidden',
        'detail' => 'You do not have permission to perform this action.',
    ],
    'not_found' => [
        'title' => 'Not found',
        'detail' => 'The requested endpoint or resource does not exist.',
    ],
    'method_not_allowed' => [
        'title' => 'Method not allowed',
        'detail' => 'This endpoint does not support that HTTP method.',
    ],
    'too_many_requests' => [
        'title' => 'Too many requests',
        'detail' => 'The rate limit has been exceeded. Retry later.',
    ],
    'server_error' => [
        'title' => 'Server error',
        'detail' => 'Something went wrong. Try again later.',
    ],
];
