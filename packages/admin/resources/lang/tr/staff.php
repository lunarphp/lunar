<?php

return [

    'label' => 'Personel',

    'plural_label' => 'Personel',

    'table' => [
        'first_name' => [
            'label' => 'Ad',
        ],
        'last_name' => [
            'label' => 'Soyad',
        ],
        'email' => [
            'label' => 'E-posta',
        ],
        'admin' => [
            'badge' => 'Süper Yönetici',
        ],
    ],

    'form' => [
        'first_name' => [
            'label' => 'Ad',
        ],
        'last_name' => [
            'label' => 'Soyad',
        ],
        'email' => [
            'label' => 'E-posta',
        ],
        'password' => [
            'label' => 'Şifre',
            'hint' => 'Şifreyi sıfırla',
        ],
        'admin' => [
            'label' => 'Süper Yönetici',
            'helper' => 'Süper yönetici rolleri yönetim panelinde değiştirilemez.',
        ],
        'roles' => [
            'label' => 'Roller',
            'helper' => ':roles tam erişime sahip',
        ],
        'permissions' => [
            'label' => 'İzinler',
        ],
        'role' => [
            'label' => 'Rol Adı',
        ],
    ],

    'action' => [
        'acl' => [
            'label' => 'Erişim Kontrolü',
        ],
        'add-role' => [
            'label' => 'Rol Ekle',
        ],
        'delete-role' => [
            'label' => 'Rolü Sil',
            'heading' => 'Rolü sil: :role',
        ],
    ],

    'acl' => [
        'title' => 'Erişim Kontrolü',
        'tooltip' => [
            'roles-included' => 'İzin şu rollere dahildir',
        ],
        'notification' => [
            'updated' => 'Güncellendi',
            'error' => 'Hata',
            'no-role' => 'Rol Lunar\'da kayıtlı değil',
            'no-permission' => 'İzin Lunar\'da kayıtlı değil',
            'no-role-permission' => 'Rol ve İzin Lunar\'da kayıtlı değil',
        ],
    ],

];
