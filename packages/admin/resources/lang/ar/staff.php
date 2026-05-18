<?php

return [

    'label' => 'موظف',

    'plural_label' => 'الموظفون',

    'table' => [
        'first_name' => [
            'label' => 'الاسم الأول',
        ],
        'last_name' => [
            'label' => 'اسم العائلة',
        ],
        'email' => [
            'label' => 'الايميل',
        ],
        'admin' => [
            'badge' => 'المسؤول الأعلى',
        ],
    ],

    'form' => [
        'first_name' => [
            'label' => 'الاسم الأول',
        ],
        'last_name' => [
            'label' => 'اسم العائلة',
        ],
        'email' => [
            'label' => 'الايميل',
        ],
        'password' => [
            'label' => 'كلمة المرور',
            'hint' => 'إعادة تعيين كلمة المرور',
        ],
        'admin' => [
            'label' => 'المسؤول الأعلى',
            'helper' => 'لا يمكن تغيير أدوار المسؤول الأعلى في لوحة التحكم',
        ],
        'roles' => [
            'label' => 'الرتب',
            'helper' => ':roles لديهم وصول كامل',
        ],
        'permissions' => [
            'label' => 'الصلاحيات',
        ],
        'role' => [
            'label' => 'اسم الرتبة',
        ],
    ],

    'action' => [
        'acl' => [
            'label' => 'التحكم في الصلاحيات',
        ],
        'add-role' => [
            'label' => 'اضافة رتبة',
        ],
        'delete-role' => [
            'label' => 'حذف رتبة',
            'heading' => 'حذف الرتبة: :role',
        ],
    ],

    'acl' => [
        'title' => 'التحكم في الصلاحيات',
        'tooltip' => [
            'roles-included' => 'الصلاحية متضمنة في الرتب التالية',
        ],
        'notification' => [
            'updated' => 'تم التحديث',
            'error' => 'خطأ',
            'no-role' => 'الرتبة غير مسجّله',
            'no-permission' => 'الصلاحية غير مسجّله',
            'no-role-permission' => 'الرتبة والصلاحية غير مسجّلين',
        ],
    ],

];
