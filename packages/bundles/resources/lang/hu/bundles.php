<?php

return [
    'selection' => [
        'malformed' => 'A csomag kiválasztása hibás formátumú.',
        'unknown_group' => 'A csomagnak nincs ":id" opciócsoportja.',
        'unknown_component' => 'A(z) ":id" opció nem érhető el a(z) ":group" csoportban.',
        'duplicate_component' => 'A(z) ":group" csoport egyik opcióját többször választották ki.',
        'count' => 'Válasszon :min és :max közötti számú opciót a(z) ":group" csoportban.',
        'unavailable' => 'A(z) ":identifier" csomagösszetevő nem érhető el ebben a mennyiségben.',
    ],
    'nesting' => [
        'is_component' => 'Egy másik csomag részét képező változat maga nem lehet csomag.',
        'is_bundle' => 'Egy csomag nem tartalmazhat másik csomagot.',
        'self' => 'Egy csomag nem tartalmazhatja a saját változatát.',
    ],
    'definition' => [
        'empty' => 'A csomagnak legalább egy összetevőre van szüksége.',
        'too_many_components' => 'Egy csomag legfeljebb :max összetevőt tartalmazhat.',
        'unknown_group' => 'Az opciócsoport nem tartozik ehhez a csomaghoz.',
        'group_selections' => 'A(z) ":group" kiválasztási korlátainak teljesíteniük kell: minimum <= maximum <= opciók száma.',
    ],
    'pricing' => [
        'missing_component_price' => 'A(z) ":identifier" összetevőnek nincs ára :currency pénznemben, így a csomag nem árazható be ebben a pénznemben.',
    ],
    'console' => [
        'repriced' => ':count csomag újraárazva.',
    ],
];
