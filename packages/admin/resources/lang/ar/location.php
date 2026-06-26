<?php

return [
    'label' => 'الموقع',

    'plural_label' => 'المواقع',

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'لا يمكن حذف هذا الموقع لوجود عمليات تنفيذ مرتبطة به.',
            ],
        ],
    ],
];
