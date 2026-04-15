<?php

return [

    'label' => 'کارمند',

    'plural_label' => 'کارمندان',

    'table' => [
        'first_name' => [
            'label' => 'نام',
        ],
        'last_name' => [
            'label' => 'نام خانوادگی',
        ],
        'email' => [
            'label' => 'ایمیل',
        ],
        'admin' => [
            'badge' => 'سوپر ادمین',
        ],
    ],

    'form' => [
        'first_name' => [
            'label' => 'نام',
        ],
        'last_name' => [
            'label' => 'نام خانوادگی',
        ],
        'email' => [
            'label' => 'ایمیل',
        ],
        'password' => [
            'label' => 'رمز عبور',
            'hint' => 'بازنشانی رمز عبور',
        ],
        'admin' => [
            'label' => 'سوپر ادمین',
            'helper' => 'نقش‌های سوپر ادمین در هاب قابل تغییر نیستند.',
        ],
        'roles' => [
            'label' => 'نقش‌ها',
            'helper' => ':roles دسترسی کامل دارند',
        ],
        'permissions' => [
            'label' => 'مجوزها',
        ],
        'role' => [
            'label' => 'نام نقش',
        ],
    ],

    'action' => [
        'acl' => [
            'label' => 'کنترل دسترسی',
        ],
        'add-role' => [
            'label' => 'افزودن نقش',
        ],
        'delete-role' => [
            'label' => 'حذف نقش',
            'heading' => 'حذف نقش: :role',
        ],
    ],

    'acl' => [
        'title' => 'کنترل دسترسی',
        'tooltip' => [
            'roles-included' => 'این مجوز در نقش‌های زیر گنجانده شده است',
        ],
        'notification' => [
            'updated' => 'به‌روزرسانی شد',
            'error' => 'خطا',
            'no-role' => 'نقش در لونا ثبت نشده است',
            'no-permission' => 'مجوز در لونا ثبت نشده است',
            'no-role-permission' => 'نقش و مجوز در لونا ثبت نشده‌اند',
        ],
    ],

];
