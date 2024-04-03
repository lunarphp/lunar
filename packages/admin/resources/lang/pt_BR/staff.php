<?php

return [

    'label' => 'Equipe',

    'plural_label' => 'Equipe',

    'table' => [
        'firstname' => [
            'label' => 'Primeiro Nome',
        ],
        'lastname' => [
            'label' => 'Sobrenome',
        ],
        'email' => [
            'label' => 'E-mail',
        ],
        'admin' => [
            'badge' => 'Super Administrador',
        ],
    ],

    'form' => [
        'firstname' => [
            'label' => 'Primeiro Nome',
        ],
        'lastname' => [
            'label' => 'Sobrenome',
        ],
        'email' => [
            'label' => 'E-mail',
        ],
        'password' => [
            'label' => 'Senha',
            'hint' => 'Redefinir senha',
        ],
        'admin' => [
            'label' => 'Super Administrador',
            'helper' => 'Os papéis de super administrador não podem ser alterados no hub.',
        ],
        'roles' => [
            'label' => 'Funções',
            'helper' => ':roles têm acesso total',
        ],
        'permissions' => [
            'label' => 'Permissões',
        ],
        'role' => [
            'label' => 'Nome da Função',
        ],
    ],

    'action' => [
        'acl' => [
            'label' => 'Controle de Acesso',
        ],
        'add-role' => [
            'label' => 'Adicionar Função',
        ],
        'delete-role' => [
            'label' => 'Excluir Função',
            'heading' => 'Excluir função: :role',
        ],
    ],

    'acl' => [
        'title' => 'Controle de Acesso',
        'tooltip' => [
            'roles-included' => 'A permissão está incluída nas seguintes funções',
        ],
        'notification' => [
            'updated' => 'Atualizado',
            'error' => 'Erro',
            'no-role' => 'Função não registrada no Lunar',
            'no-permission' => 'Permissão não registrada no Lunar',
            'no-role-permission' => 'Função e permissão não registradas no Lunar',
        ],
    ],

];
