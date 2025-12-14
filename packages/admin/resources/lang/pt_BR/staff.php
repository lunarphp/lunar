<?php

return [

    'label' => 'Equipe',

    'plural_label' => 'Equipe',

    'table' => [
        'first_name' => [
            'label' => 'Nome',
        ],
        'last_name' => [
            'label' => 'Sobrenome',
        ],
        'email' => [
            'label' => 'E-mail',
        ],
        'admin' => [
            'badge' => 'Super Admin',
        ],
    ],

    'form' => [
        'first_name' => [
            'label' => 'Nome',
        ],
        'last_name' => [
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
            'label' => 'Super Admin',
            'helper' => 'Funções de super admin não podem ser alteradas no hub.',
        ],
        'roles' => [
            'label' => 'Funções',
            'helper' => ':roles têm acesso total',
        ],
        'permissions' => [
            'label' => 'Permissões',
        ],
        'role' => [
            'label' => 'Nome da função',
        ],
    ],

    'action' => [
        'acl' => [
            'label' => 'Controle de acesso',
        ],
        'add-role' => [
            'label' => 'Adicionar função',
        ],
        'delete-role' => [
            'label' => 'Excluir função',
            'heading' => 'Excluir função: :role',
        ],
    ],

    'acl' => [
        'title' => 'Controle de acesso',
        'tooltip' => [
            'roles-included' => 'Permissão incluída nas seguintes funções',
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
