<?php

return [

    'order_update' => [
        'label' => 'Aktualizacja zamówienia',
        'subject' => 'Aktualizacja dotycząca zamówienia :reference',
        'greeting' => 'Dzień dobry,',
        'intro' => 'Chcemy poinformować Cię o stanie zamówienia :reference.',
        'outro' => 'Dziękujemy za zakupy.',
    ],

    'order_confirmation' => [
        'label' => 'Potwierdzenie zamówienia',
        'subject' => 'Otrzymaliśmy Twoje zamówienie :reference',
        'heading' => 'Dziękujemy za zamówienie',
        'intro' => 'Otrzymaliśmy Twoje zamówienie :reference złożone :date. Poniżej znajdziesz podsumowanie zamówienia.',
        'outro' => 'Poinformujemy Cię, gdy tylko zamówienie zostanie wysłane.',
    ],

    'payment_received' => [
        'label' => 'Płatność otrzymana',
        'subject' => 'Otrzymaliśmy płatność za zamówienie :reference',
        'heading' => 'Płatność otrzymana',
        'intro' => 'Otrzymaliśmy Twoją płatność za zamówienie :reference.',
        'outro' => 'Dziękujemy. Skontaktujemy się, gdy zamówienie zostanie wysłane.',
    ],

    'order_shipped' => [
        'label' => 'Zamówienie wysłane',
        'subject' => 'Twoje zamówienie :reference jest w drodze',
        'heading' => 'Twoje zamówienie jest w drodze',
        'intro' => 'Dobra wiadomość: następujące produkty z zamówienia :reference zostały wysłane.',
        'tracking' => 'Możesz śledzić przesyłkę, korzystając z poniższych danych.',
        'outro' => 'Dziękujemy za zakupy.',
    ],

    'order_ready_for_collection' => [
        'label' => 'Gotowe do odbioru',
        'subject' => 'Twoje zamówienie :reference jest gotowe do odbioru',
        'heading' => 'Twoje zamówienie jest gotowe do odbioru',
        'intro' => 'Następujące produkty z zamówienia :reference czekają na odbiór.',
        'outro' => 'Prosimy o podanie numeru zamówienia przy odbiorze.',
    ],

    'order_provisioned' => [
        'label' => 'Zamówienie udostępnione',
        'subject' => 'Twoje zamówienie :reference jest gotowe',
        'heading' => 'Twoje produkty cyfrowe są gotowe',
        'intro' => 'Następujące produkty z zamówienia :reference są już dostępne.',
        'outro' => 'Dziękujemy za zakupy.',
    ],

    'return_received' => [
        'label' => 'Zwrot otrzymany',
        'subject' => 'Otrzymaliśmy Twój zwrot do zamówienia :reference',
        'heading' => 'Zwrot otrzymany',
        'intro' => 'Otrzymaliśmy zwrot następujących produktów z zamówienia :reference.',
        'outro' => 'Jeśli przysługuje Ci zwrot pieniędzy, potwierdzimy go w osobnej wiadomości.',
    ],

    'order_cancelled' => [
        'label' => 'Zamówienie anulowane',
        'subject' => 'Twoje zamówienie :reference zostało anulowane',
        'heading' => 'Twoje zamówienie zostało anulowane',
        'intro' => 'Twoje zamówienie :reference zostało anulowane.',
        'outro' => 'W razie pytań wystarczy odpowiedzieć na tę wiadomość.',
    ],

    'refund_issued' => [
        'label' => 'Zwrot pieniędzy wykonany',
        'subject' => 'Wykonano zwrot pieniędzy za zamówienie :reference',
        'heading' => 'Zwrot pieniędzy wykonany',
        'intro' => 'Wykonaliśmy zwrot pieniędzy za Twoje zamówienie :reference.',
        'outro' => 'W zależności od operatora płatności zwrot może pojawić się na koncie po kilku dniach.',
    ],

    'partial_fulfilment_update' => [
        'label' => 'Wysyłka częściowa',
        'subject' => 'Aktualizacja dotycząca pozostałej części zamówienia :reference',
        'heading' => 'Aktualizacja dotycząca zamówienia',
        'intro' => 'Część zamówienia :reference została już wysłana. Pozostałe produkty są jeszcze przygotowywane.',
        'outstanding' => 'Następujące produkty zostaną wysłane później:',
        'outro' => 'Przepraszamy za opóźnienie. Poinformujemy Cię, gdy tylko zostaną wysłane.',
    ],

    'partials' => [
        'greeting' => 'Cześć :name,',
        'greeting_fallback' => 'Dzień dobry,',
        'signoff' => 'Pozdrawiamy,',
        'item' => 'Produkt',
        'quantity' => 'Ilość',
        'total' => 'Razem',
        'sub_total' => 'Suma częściowa',
        'discount' => 'Rabat',
        'shipping' => 'Wysyłka',
        'tax' => 'Podatek',
        'order_total' => 'Wartość zamówienia',
        'carrier' => 'Przewoźnik',
        'tracking_number' => 'Numer przesyłki',
        'track' => 'Śledź przesyłkę',
        'shipping_address' => 'Adres dostawy',
        'billing_address' => 'Adres rozliczeniowy',
        'payment_status' => 'Status płatności',
        'collect_from' => 'Odbiór w',
        'reason' => 'Powód',
        'refund_amount' => 'Kwota zwrotu',
    ],

];
