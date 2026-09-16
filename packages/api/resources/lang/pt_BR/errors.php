<?php

return [
    'invalid_query' => [
        'title' => 'Consulta inválida',
    ],
    'query' => [
        'malformed_parameter' => 'O parâmetro :parameter está malformado.',
        'unknown_include' => 'Inclusão desconhecida ":value" em :type. Permitidas: :allowed.',
        'include_too_deep' => 'A inclusão ":value" excede a profundidade máxima de :max.',
        'unknown_type' => 'Tipo de recurso desconhecido ":value". Permitidos: :allowed.',
        'unknown_field' => 'Campo desconhecido ":value" em :type. Permitidos: :allowed.',
        'unknown_filter' => 'Filtro desconhecido ":value". Permitidos: :allowed.',
        'unknown_operator' => 'Operador desconhecido ":value" para o filtro ":filter". Permitidos: :allowed.',
        'unknown_sort' => 'Ordenação desconhecida ":value". Permitidas: :allowed.',
        'invalid_page_size' => 'page[size] deve ser um número inteiro entre 1 e :max.',
        'invalid_page_number' => 'page[number] deve ser um número inteiro positivo.',
        'cursor_unsupported' => 'O recurso :type não oferece suporte à paginação por cursor.',
        'cursor_and_number' => 'page[cursor] e page[number] não podem ser combinados.',
        'invalid_cursor' => 'page[cursor] não é um cursor válido.',
        'unknown_page_key' => 'Chave de paginação desconhecida ":value". Permitidas: number, size, cursor.',
    ],
    'resource_not_found' => [
        'title' => 'Não encontrado',
        'detail' => 'Não existe um recurso :type com o id ":id".',
    ],
    'invalid_header' => [
        'title' => 'Cabeçalho inválido',
        'detail' => 'O valor ":value" do cabeçalho :header não foi reconhecido.',
    ],
    'invalid_cart_token' => [
        'title' => 'Token de carrinho inválido',
        'detail' => 'O token X-Lunar-Cart é inválido ou expirou.',
    ],
    'cart_not_found' => [
        'title' => 'Carrinho não encontrado',
        'detail' => 'O carrinho referenciado por X-Lunar-Cart não existe mais.',
    ],
    'customer_not_found' => [
        'title' => 'Sem cliente',
        'detail' => 'O usuário autenticado não possui um registro de cliente.',
    ],
    'validation_failed' => [
        'title' => 'Falha na validação',
    ],
    'unauthenticated' => [
        'title' => 'Não autenticado',
        'detail' => 'Uma credencial válida é obrigatória.',
    ],
    'forbidden' => [
        'title' => 'Proibido',
        'detail' => 'Você não tem permissão para executar esta ação.',
    ],
    'not_found' => [
        'title' => 'Não encontrado',
        'detail' => 'O endpoint ou recurso solicitado não existe.',
    ],
    'method_not_allowed' => [
        'title' => 'Método não permitido',
        'detail' => 'Este endpoint não oferece suporte a esse método HTTP.',
    ],
    'too_many_requests' => [
        'title' => 'Muitas solicitações',
        'detail' => 'O limite de solicitações foi excedido. Tente novamente mais tarde.',
    ],
    'server_error' => [
        'title' => 'Erro do servidor',
        'detail' => 'Algo deu errado. Tente novamente mais tarde.',
    ],
];
