<?php

return [
    'label' => 'Ubicación',

    'plural_label' => 'Ubicaciones',

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Esta ubicación no se puede eliminar porque tiene cumplimientos asignados.',
            ],
        ],
    ],
];
