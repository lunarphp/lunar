<?php

return [

    'label' => 'Nhân viên',

    'plural_label' => 'Nhân viên',

    'table' => [
        'firstname' => [
            'label' => 'Họ',
        ],
        'lastname' => [
            'label' => 'Tên',
        ],
        'email' => [
            'label' => 'Email',
        ],
        'admin' => [
            'badge' => 'Siêu quản trị',
        ],
    ],

    'form' => [
        'firstname' => [
            'label' => 'Họ',
        ],
        'lastname' => [
            'label' => 'Tên',
        ],
        'email' => [
            'label' => 'Email',
        ],
        'password' => [
            'label' => 'Mật khẩu',
            'hint' => 'Đặt lại mật khẩu',
        ],
        'admin' => [
            'label' => 'Siêu quản trị',
            'helper' => 'Vai trò siêu quản trị không thể thay đổi trong hub.',
        ],
        'roles' => [
            'label' => 'Vai trò',
            'helper' => ':roles có quyền truy cập đầy đủ',
        ],
        'permissions' => [
            'label' => 'Quyền hạn',
        ],
        'role' => [
            'label' => 'Tên vai trò',
        ],
    ],

    'action' => [
        'acl' => [
            'label' => 'Kiểm soát Truy cập',
        ],
        'add-role' => [
            'label' => 'Thêm Vai trò',
        ],
        'delete-role' => [
            'label' => 'Xóa Vai trò',
            'heading' => 'Xóa vai trò: :role',
        ],
    ],

    'acl' => [
        'title' => 'Kiểm soát Truy cập',
        'tooltip' => [
            'roles-included' => 'Quyền hạn được bao gồm trong các vai trò sau',
        ],
        'notification' => [
            'updated' => 'Đã cập nhật',
            'error' => 'Lỗi',
            'no-role' => 'Vai trò chưa đăng ký trong Lunar',
            'no-permission' => 'Quyền hạn chưa đăng ký trong Lunar',
            'no-role-permission' => 'Vai trò và Quyền hạn chưa đăng ký trong Lunar',
        ],
    ],

];
