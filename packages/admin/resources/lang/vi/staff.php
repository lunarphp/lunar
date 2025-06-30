<?php

return [

    'label' => 'Nhân viên',

    'plural_label' => 'Nhân viên',

    'table' => [
        'firstname' => [
            'label' => 'Tên',
        ],
        'lastname' => [
            'label' => 'Họ',
        ],
        'email' => [
            'label' => 'Email',
        ],
        'admin' => [
            'badge' => 'Quản trị viên cao cấp',
        ],
    ],

    'form' => [
        'firstname' => [
            'label' => 'Tên',
        ],
        'lastname' => [
            'label' => 'Họ',
        ],
        'email' => [
            'label' => 'Email',
        ],
        'password' => [
            'label' => 'Mật khẩu',
            'hint' => 'Đặt lại mật khẩu',
        ],
        'admin' => [
            'label' => 'Quản trị viên cao cấp',
            'helper' => 'Vai trò quản trị viên cao cấp không thể thay đổi trong hub.',
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
            'label' => 'Kiểm soát truy cập',
        ],
        'add-role' => [
            'label' => 'Thêm vai trò',
        ],
        'delete-role' => [
            'label' => 'Xóa vai trò',
            'heading' => 'Xóa vai trò: :role',
        ],
    ],

    'acl' => [
        'title' => 'Kiểm soát truy cập',
        'tooltip' => [
            'roles-included' => 'Quyền này được bao gồm trong các vai trò sau',
        ],
        'notification' => [
            'updated' => 'Đã cập nhật',
            'error' => 'Lỗi',
            'no-role' => 'Vai trò chưa được đăng ký trong Lunar',
            'no-permission' => 'Quyền chưa được đăng ký trong Lunar',
            'no-role-permission' => 'Vai trò và quyền chưa được đăng ký trong Lunar',
        ],
    ],

];
