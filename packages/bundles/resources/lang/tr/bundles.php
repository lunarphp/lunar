<?php

return [
    'selection' => [
        'malformed' => 'Paket seçimi hatalı biçimlendirilmiş.',
        'unknown_group' => 'Pakette ":id" adlı bir seçenek grubu yok.',
        'unknown_component' => '":id" seçeneği ":group" içinde mevcut değil.',
        'duplicate_component' => '":group" içindeki bir seçenek birden fazla kez seçildi.',
        'count' => '":group" içinde :min ile :max arasında seçenek seçin.',
        'unavailable' => 'Paket bileşeni ":identifier" bu miktarda mevcut değil.',
    ],
    'nesting' => [
        'is_component' => 'Başka bir paketin parçası olan bir varyant kendisi paket olamaz.',
        'is_bundle' => 'Bir paket başka bir paket içeremez.',
        'self' => 'Bir paket kendi varyantını içeremez.',
    ],
    'definition' => [
        'empty' => 'Bir paketin en az bir bileşeni olmalıdır.',
        'too_many_components' => 'Bir paket en fazla :max bileşen içerebilir.',
        'unknown_group' => 'Seçenek grubu bu pakete ait değil.',
        'group_selections' => '":group" için seçim sınırları minimum <= maksimum <= seçenek sayısı koşulunu sağlamalıdır.',
    ],
    'pricing' => [
        'missing_component_price' => '":identifier" bileşeninin :currency cinsinden fiyatı yok, bu nedenle paket bu para biriminde fiyatlandırılamaz.',
    ],
    'console' => [
        'repriced' => ':count paketin fiyatı yeniden hesaplandı.',
    ],
];
