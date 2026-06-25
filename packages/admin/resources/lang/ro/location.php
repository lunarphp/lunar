<?php

return [
    'label' => 'Locație',

    'plural_label' => 'Locații',

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Această locație nu poate fi ștearsă deoarece există onorări atribuite ei.',
            ],
        ],
    ],
];
