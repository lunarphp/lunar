<?php

return [
    'selection' => [
        'malformed' => 'A seleção do pacote está malformada.',
        'unknown_group' => 'O pacote não possui o grupo de opções ":id".',
        'unknown_component' => 'A opção ":id" não está disponível em ":group".',
        'duplicate_component' => 'Uma opção em ":group" foi escolhida mais de uma vez.',
        'count' => 'Escolha entre :min e :max opções em ":group".',
        'unavailable' => 'O componente do pacote ":identifier" não está disponível nesta quantidade.',
    ],
    'nesting' => [
        'is_component' => 'Uma variante que faz parte de outro pacote não pode ser um pacote.',
        'is_bundle' => 'Um pacote não pode conter outro pacote.',
        'self' => 'Um pacote não pode conter sua própria variante.',
    ],
    'definition' => [
        'empty' => 'Um pacote precisa de pelo menos um componente.',
        'too_many_components' => 'Um pacote não pode ter mais de :max componentes.',
        'unknown_group' => 'O grupo de opções não pertence a este pacote.',
        'group_selections' => 'Os limites de seleção de ":group" devem respeitar mínimo <= máximo <= número de opções.',
    ],
    'pricing' => [
        'missing_component_price' => 'O componente ":identifier" não tem preço em :currency, portanto o pacote não pode ser precificado nessa moeda.',
    ],
    'console' => [
        'repriced' => ':count pacotes reprecificados.',
    ],
];
