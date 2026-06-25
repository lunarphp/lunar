<?php

return [
    'label' => 'Local',

    'plural_label' => 'Locais',

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Este local não pode ser excluído porque há fulfillments atribuídos a ele.',
            ],
        ],
    ],
];
