<?php

return [
    'label' => 'Konum',

    'plural_label' => 'Konumlar',

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Bu konuma atanmış karşılamalar bulunduğundan konum silinemez.',
            ],
        ],
    ],
];
