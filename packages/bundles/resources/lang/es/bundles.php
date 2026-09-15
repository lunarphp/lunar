<?php

return [
    'selection' => [
        'malformed' => 'La selección del paquete no es válida.',
        'unknown_group' => 'El paquete no tiene ningún grupo de opciones ":id".',
        'unknown_component' => 'La opción ":id" no está disponible en ":group".',
        'duplicate_component' => 'Una opción de ":group" se eligió más de una vez.',
        'count' => 'Elige entre :min y :max opciones en ":group".',
        'unavailable' => 'El componente del paquete ":identifier" no está disponible en esta cantidad.',
    ],
    'nesting' => [
        'is_component' => 'Una variante que forma parte de otro paquete no puede ser un paquete.',
        'is_bundle' => 'Un paquete no puede contener otro paquete.',
        'self' => 'Un paquete no puede contener su propia variante.',
    ],
    'definition' => [
        'empty' => 'Un paquete necesita al menos un componente.',
        'too_many_components' => 'Un paquete no puede tener más de :max componentes.',
        'unknown_group' => 'El grupo de opciones no pertenece a este paquete.',
        'group_selections' => 'Los límites de selección de ":group" deben cumplir mínimo <= máximo <= número de opciones.',
    ],
    'pricing' => [
        'missing_component_price' => 'El componente ":identifier" no tiene precio en :currency, por lo que el paquete no se puede valorar en esa moneda.',
    ],
    'console' => [
        'repriced' => 'Se han recalculado los precios de :count paquetes.',
    ],
];
