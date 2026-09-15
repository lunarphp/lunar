<?php

return [
    'selection' => [
        'malformed' => 'Selecția pachetului este invalidă.',
        'unknown_group' => 'Pachetul nu are grupul de opțiuni ":id".',
        'unknown_component' => 'Opțiunea ":id" nu este disponibilă în ":group".',
        'duplicate_component' => 'O opțiune din ":group" a fost aleasă de mai multe ori.',
        'count' => 'Alegeți între :min și :max opțiuni în ":group".',
        'unavailable' => 'Componenta pachetului ":identifier" nu este disponibilă în această cantitate.',
    ],
    'nesting' => [
        'is_component' => 'O variantă care face parte din alt pachet nu poate fi la rândul ei un pachet.',
        'is_bundle' => 'Un pachet nu poate conține un alt pachet.',
        'self' => 'Un pachet nu poate conține propria variantă.',
    ],
    'definition' => [
        'empty' => 'Un pachet are nevoie de cel puțin o componentă.',
        'too_many_components' => 'Un pachet nu poate avea mai mult de :max componente.',
        'unknown_group' => 'Grupul de opțiuni nu aparține acestui pachet.',
        'group_selections' => 'Limitele de selecție pentru ":group" trebuie să respecte minim <= maxim <= numărul de opțiuni.',
    ],
    'pricing' => [
        'missing_component_price' => 'Componenta ":identifier" nu are preț în :currency, așa că pachetul nu poate fi evaluat în această monedă.',
    ],
    'console' => [
        'repriced' => 'Prețurile pentru :count pachete au fost recalculate.',
    ],
];
