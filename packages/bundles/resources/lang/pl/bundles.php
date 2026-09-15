<?php

return [
    'selection' => [
        'malformed' => 'Wybór zestawu jest nieprawidłowy.',
        'unknown_group' => 'Zestaw nie ma grupy opcji ":id".',
        'unknown_component' => 'Opcja ":id" nie jest dostępna w ":group".',
        'duplicate_component' => 'Opcja w ":group" została wybrana więcej niż raz.',
        'count' => 'Wybierz od :min do :max opcji w ":group".',
        'unavailable' => 'Składnik zestawu ":identifier" nie jest dostępny w tej ilości.',
    ],
    'nesting' => [
        'is_component' => 'Wariant będący częścią innego zestawu nie może sam być zestawem.',
        'is_bundle' => 'Zestaw nie może zawierać innego zestawu.',
        'self' => 'Zestaw nie może zawierać własnego wariantu.',
    ],
    'definition' => [
        'empty' => 'Zestaw wymaga co najmniej jednego składnika.',
        'too_many_components' => 'Zestaw nie może mieć więcej niż :max składników.',
        'unknown_group' => 'Grupa opcji nie należy do tego zestawu.',
        'group_selections' => 'Limity wyboru dla ":group" muszą spełniać warunek minimum <= maksimum <= liczba opcji.',
    ],
    'pricing' => [
        'missing_component_price' => 'Składnik ":identifier" nie ma ceny w :currency, więc zestaw nie może zostać wyceniony w tej walucie.',
    ],
    'console' => [
        'repriced' => 'Przeliczono ceny :count zestawów.',
    ],
];
