<?php

return [
    'label' => 'Emplacement',

    'plural_label' => 'Emplacements',

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Cet emplacement ne peut pas être supprimé car des traitements y sont rattachés.',
            ],
        ],
    ],
];
