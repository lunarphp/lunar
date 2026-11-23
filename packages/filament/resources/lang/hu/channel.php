<?php

return [
    'label' => 'Csatorna',
    'plural_label' => 'Csatornák',
    'table' => [
        'name' => [
            'label' => 'Név',
        ],
        'handle' => [
            'label' => 'Azonosító',
        ],
        'url' => [
            'label' => 'URL',
        ],
        'default' => [
            'label' => 'Alapértelmezett',
        ],
    ],
    'form' => [
        'name' => [
            'label' => 'Név',
        ],
        'handle' => [
            'label' => 'Azonosító',
        ],
        'url' => [
            'label' => 'URL',
        ],
        'default' => [
            'label' => 'Alapértelmezett',
        ],
    ],

    'actions' => [
        'delete' => [
            'blocked' => 'Az alapértelmezett csatorna, a rendelési előzményekkel rendelkező csatornák és azok a csatornák, amelyeket még egy régió vagy kosár használ, nem törölhetők. Helyette állítsa a csatornát inaktívra.',
        ],
    ],

];
