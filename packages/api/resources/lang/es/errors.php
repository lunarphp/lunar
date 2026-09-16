<?php

return [
    'invalid_query' => [
        'title' => 'Consulta inválida',
    ],
    'query' => [
        'malformed_parameter' => 'El parámetro :parameter está mal formado.',
        'unknown_include' => 'Inclusión desconocida ":value" en :type. Permitidas: :allowed.',
        'include_too_deep' => 'La inclusión ":value" supera la profundidad máxima de :max.',
        'unknown_type' => 'Tipo de recurso desconocido ":value". Permitidos: :allowed.',
        'unknown_field' => 'Campo desconocido ":value" en :type. Permitidos: :allowed.',
        'unknown_filter' => 'Filtro desconocido ":value". Permitidos: :allowed.',
        'unknown_operator' => 'Operador desconocido ":value" para el filtro ":filter". Permitidos: :allowed.',
        'unknown_sort' => 'Orden desconocido ":value". Permitidos: :allowed.',
        'invalid_page_size' => 'page[size] debe ser un número entero entre 1 y :max.',
        'invalid_page_number' => 'page[number] debe ser un número entero positivo.',
        'cursor_unsupported' => 'El recurso :type no admite paginación por cursor.',
        'cursor_and_number' => 'page[cursor] y page[number] no se pueden combinar.',
        'invalid_cursor' => 'page[cursor] no es un cursor válido.',
        'unknown_page_key' => 'Clave de paginación desconocida ":value". Permitidas: number, size, cursor.',
    ],
    'resource_not_found' => [
        'title' => 'No encontrado',
        'detail' => 'No existe ningún recurso :type con el id ":id".',
    ],
    'invalid_header' => [
        'title' => 'Encabezado inválido',
        'detail' => 'El valor ":value" del encabezado :header no se reconoce.',
    ],
    'invalid_cart_token' => [
        'title' => 'Token de carrito inválido',
        'detail' => 'El token X-Lunar-Cart es inválido o ha expirado.',
    ],
    'cart_not_found' => [
        'title' => 'Carrito no encontrado',
        'detail' => 'El carrito referenciado por X-Lunar-Cart ya no existe.',
    ],
    'customer_not_found' => [
        'title' => 'Sin cliente',
        'detail' => 'El usuario autenticado no tiene un registro de cliente.',
    ],
    'validation_failed' => [
        'title' => 'La validación falló',
    ],
    'unauthenticated' => [
        'title' => 'No autenticado',
        'detail' => 'Se requiere una credencial válida.',
    ],
    'forbidden' => [
        'title' => 'Prohibido',
        'detail' => 'No tiene permiso para realizar esta acción.',
    ],
    'not_found' => [
        'title' => 'No encontrado',
        'detail' => 'El endpoint o recurso solicitado no existe.',
    ],
    'method_not_allowed' => [
        'title' => 'Método no permitido',
        'detail' => 'Este endpoint no admite ese método HTTP.',
    ],
    'too_many_requests' => [
        'title' => 'Demasiadas solicitudes',
        'detail' => 'Se ha superado el límite de solicitudes. Inténtelo más tarde.',
    ],
    'server_error' => [
        'title' => 'Error del servidor',
        'detail' => 'Algo salió mal. Inténtelo más tarde.',
    ],
];
