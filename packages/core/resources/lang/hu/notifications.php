<?php

return [

    'order_update' => [
        'label' => 'Rendelésfrissítés',
        'subject' => 'Friss információ a(z) :reference rendeléséről',
        'greeting' => 'Üdvözöljük!',
        'intro' => 'Szeretnénk tájékoztatni a(z) :reference rendelésének állapotáról.',
        'outro' => 'Köszönjük a vásárlást.',
    ],

    'order_confirmation' => [
        'label' => 'Rendelés-visszaigazolás',
        'subject' => 'Megkaptuk a(z) :reference rendelését',
        'heading' => 'Köszönjük a rendelését',
        'intro' => 'Megkaptuk a(z) :reference rendelését, amelyet :date napon adott le. Az alábbiakban a rendelés összefoglalója látható.',
        'outro' => 'Értesítjük, amint a rendelése útnak indul.',
    ],

    'payment_received' => [
        'label' => 'Fizetés beérkezett',
        'subject' => 'Fizetés beérkezett a(z) :reference rendeléshez',
        'heading' => 'Fizetés beérkezett',
        'intro' => 'Megkaptuk a(z) :reference rendelés kifizetését.',
        'outro' => 'Köszönjük. Jelentkezünk, amint a rendelése útnak indul.',
    ],

    'order_shipped' => [
        'label' => 'Rendelés kiszállítva',
        'subject' => 'A(z) :reference rendelése úton van',
        'heading' => 'A rendelése úton van',
        'intro' => 'Jó hír: a(z) :reference rendelés alábbi tételeit feladtuk.',
        'tracking' => 'A kézbesítést az alábbi nyomkövetési adatokkal követheti.',
        'outro' => 'Köszönjük a vásárlást.',
    ],

    'order_ready_for_collection' => [
        'label' => 'Átvételre kész',
        'subject' => 'A(z) :reference rendelése átvehető',
        'heading' => 'A rendelése átvehető',
        'intro' => 'A(z) :reference rendelés alábbi tételei átvételre készen állnak.',
        'outro' => 'Kérjük, átvételkor hozza magával a rendelési számát.',
    ],

    'order_provisioned' => [
        'label' => 'Rendelés hozzáférhetővé téve',
        'subject' => 'A(z) :reference rendelése elkészült',
        'heading' => 'A digitális tételei elkészültek',
        'intro' => 'A(z) :reference rendelés alábbi tételei mostantól elérhetők.',
        'outro' => 'Köszönjük a vásárlást.',
    ],

    'return_received' => [
        'label' => 'Visszaküldés beérkezett',
        'subject' => 'Megkaptuk a(z) :reference rendelés visszaküldését',
        'heading' => 'Visszaküldés beérkezett',
        'intro' => 'A(z) :reference rendelés alábbi tételei visszaérkeztek hozzánk.',
        'outro' => 'Ha visszatérítés jár, külön e-mailben igazoljuk vissza.',
    ],

    'order_cancelled' => [
        'label' => 'Rendelés visszavonva',
        'subject' => 'A(z) :reference rendelését visszavontuk',
        'heading' => 'A rendelését visszavontuk',
        'intro' => 'A(z) :reference rendelését visszavontuk.',
        'outro' => 'Ha kérdése van, válaszoljon erre az e-mailre.',
    ],

    'refund_issued' => [
        'label' => 'Visszatérítés elindítva',
        'subject' => 'Visszatérítést indítottunk a(z) :reference rendeléshez',
        'heading' => 'Visszatérítés elindítva',
        'intro' => 'Visszatérítést indítottunk a(z) :reference rendeléséhez.',
        'outro' => 'A fizetési szolgáltatótól függően a visszatérítés néhány nap múlva jelenhet meg.',
    ],

    'partial_fulfilment_update' => [
        'label' => 'Részteljesítés',
        'subject' => 'Friss információ a(z) :reference rendelés fennmaradó részéről',
        'heading' => 'Friss információ a rendeléséről',
        'intro' => 'A(z) :reference rendelés egy részét már feladtuk. A fennmaradó tételek előkészítése folyamatban van.',
        'outstanding' => 'Az alábbi tételek még úton lesznek:',
        'outro' => 'Elnézést kérünk a késedelemért, és értesítjük, amint útnak indulnak.',
    ],

    'partials' => [
        'greeting' => 'Kedves :name!',
        'greeting_fallback' => 'Üdvözöljük!',
        'signoff' => 'Üdvözlettel:',
        'item' => 'Tétel',
        'quantity' => 'Mennyiség',
        'total' => 'Összesen',
        'sub_total' => 'Részösszeg',
        'discount' => 'Kedvezmény',
        'shipping' => 'Szállítás',
        'tax' => 'Adó',
        'order_total' => 'Rendelés végösszege',
        'carrier' => 'Futárszolgálat',
        'tracking_number' => 'Nyomkövetési szám',
        'track' => 'Csomag követése',
        'shipping_address' => 'Szállítási cím',
        'billing_address' => 'Számlázási cím',
        'payment_status' => 'Fizetés állapota',
        'collect_from' => 'Átvétel helye',
        'reason' => 'Indok',
        'refund_amount' => 'Visszatérített összeg',
    ],

];
