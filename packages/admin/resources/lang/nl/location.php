<?php

return [
    'label' => 'Locatie',

    'plural_label' => 'Locaties',

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Deze locatie kan niet worden verwijderd omdat er fulfilments aan toegewezen zijn.',
            ],
        ],
    ],
];
