<?php

return [

    'order_update' => [
        'label' => 'Ažuriranje narudžbe',
        'subject' => 'Novosti o vašoj narudžbi :reference',
        'greeting' => 'Pozdrav,',
        'intro' => 'Želimo vas obavijestiti o stanju vaše narudžbe :reference.',
        'outro' => 'Hvala vam na kupnji.',
    ],

    'order_confirmation' => [
        'label' => 'Potvrda narudžbe',
        'subject' => 'Vaša narudžba :reference je zaprimljena',
        'heading' => 'Hvala vam na narudžbi',
        'intro' => 'Zaprimili smo vašu narudžbu :reference od :date. Ovdje je sažetak onoga što ste naručili.',
        'outro' => 'Javit ćemo vam se čim vaša narudžba krene na put.',
    ],

    'payment_received' => [
        'label' => 'Uplata zaprimljena',
        'subject' => 'Uplata zaprimljena za narudžbu :reference',
        'heading' => 'Uplata zaprimljena',
        'intro' => 'Zaprimili smo vašu uplatu za narudžbu :reference.',
        'outro' => 'Hvala vam. Javit ćemo vam se kad vaša narudžba krene na put.',
    ],

    'order_shipped' => [
        'label' => 'Narudžba otpremljena',
        'subject' => 'Vaša narudžba :reference je na putu',
        'heading' => 'Vaša narudžba je na putu',
        'intro' => 'Dobre vijesti: sljedeći artikli iz vaše narudžbe :reference su otpremljeni.',
        'tracking' => 'Dostavu možete pratiti pomoću podataka za praćenje u nastavku.',
        'outro' => 'Hvala vam na kupnji.',
    ],

    'order_ready_for_collection' => [
        'label' => 'Spremno za preuzimanje',
        'subject' => 'Vaša narudžba :reference je spremna za preuzimanje',
        'heading' => 'Vaša narudžba je spremna za preuzimanje',
        'intro' => 'Sljedeći artikli iz vaše narudžbe :reference spremni su za preuzimanje.',
        'outro' => 'Molimo ponesite broj narudžbe prilikom preuzimanja.',
    ],

    'order_provisioned' => [
        'label' => 'Narudžba stavljena na raspolaganje',
        'subject' => 'Vaša narudžba :reference je spremna',
        'heading' => 'Vaši digitalni artikli su spremni',
        'intro' => 'Sljedeći artikli iz vaše narudžbe :reference sada su dostupni.',
        'outro' => 'Hvala vam na kupnji.',
    ],

    'return_received' => [
        'label' => 'Povrat zaprimljen',
        'subject' => 'Zaprimili smo vaš povrat za narudžbu :reference',
        'heading' => 'Povrat zaprimljen',
        'intro' => 'Zaprimili smo povrat sljedećih artikala iz vaše narudžbe :reference.',
        'outro' => 'Ako vam pripada povrat novca, potvrdit ćemo ga u zasebnoj e-poruci.',
    ],

    'order_cancelled' => [
        'label' => 'Narudžba otkazana',
        'subject' => 'Vaša narudžba :reference je otkazana',
        'heading' => 'Vaša narudžba je otkazana',
        'intro' => 'Vaša narudžba :reference je otkazana.',
        'outro' => 'Ako imate pitanja, odgovorite na ovu e-poruku.',
    ],

    'refund_issued' => [
        'label' => 'Povrat novca izvršen',
        'subject' => 'Izvršen je povrat novca za narudžbu :reference',
        'heading' => 'Povrat novca izvršen',
        'intro' => 'Izvršili smo povrat novca za vašu narudžbu :reference.',
        'outro' => 'Ovisno o vašem pružatelju platnih usluga, povrat može biti vidljiv tek za nekoliko dana.',
    ],

    'partial_fulfilment_update' => [
        'label' => 'Djelomična isporuka',
        'subject' => 'Novosti o ostatku vaše narudžbe :reference',
        'heading' => 'Novosti o vašoj narudžbi',
        'intro' => 'Dio vaše narudžbe :reference već je otpremljen. Preostali artikli još se pripremaju.',
        'outstanding' => 'Sljedeći artikli tek slijede:',
        'outro' => 'Ispričavamo se zbog kašnjenja i javit ćemo vam se čim krenu na put.',
    ],

    'partials' => [
        'greeting' => 'Pozdrav :name,',
        'greeting_fallback' => 'Pozdrav,',
        'signoff' => 'Srdačan pozdrav,',
        'item' => 'Artikl',
        'quantity' => 'Količina',
        'total' => 'Ukupno',
        'sub_total' => 'Međuzbroj',
        'discount' => 'Popust',
        'shipping' => 'Dostava',
        'tax' => 'Porez',
        'order_total' => 'Ukupni iznos narudžbe',
        'carrier' => 'Dostavljač',
        'tracking_number' => 'Broj za praćenje',
        'track' => 'Pratite svoj paket',
        'shipping_address' => 'Adresa dostave',
        'billing_address' => 'Adresa za račun',
        'payment_status' => 'Status plaćanja',
        'collect_from' => 'Preuzimanje u',
        'reason' => 'Razlog',
        'refund_amount' => 'Iznos povrata',
    ],

];
