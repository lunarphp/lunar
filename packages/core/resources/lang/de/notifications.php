<?php

return [

    'order_update' => [
        'label' => 'Bestellaktualisierung',
        'subject' => 'Neuigkeiten zu Ihrer Bestellung :reference',
        'greeting' => 'Hallo,',
        'intro' => 'Wir möchten Sie über den Stand Ihrer Bestellung :reference informieren.',
        'outro' => 'Vielen Dank für Ihren Einkauf.',
    ],

    'order_confirmation' => [
        'label' => 'Bestellbestätigung',
        'subject' => 'Ihre Bestellung :reference ist eingegangen',
        'heading' => 'Vielen Dank für Ihre Bestellung',
        'intro' => 'Wir haben Ihre Bestellung :reference vom :date erhalten. Hier ist eine Übersicht Ihrer Bestellung.',
        'outro' => 'Wir informieren Sie, sobald Ihre Bestellung unterwegs ist.',
    ],

    'payment_received' => [
        'label' => 'Zahlung erhalten',
        'subject' => 'Zahlung für Bestellung :reference erhalten',
        'heading' => 'Zahlung erhalten',
        'intro' => 'Wir haben Ihre Zahlung für die Bestellung :reference erhalten.',
        'outro' => 'Vielen Dank. Wir melden uns, sobald Ihre Bestellung unterwegs ist.',
    ],

    'order_shipped' => [
        'label' => 'Bestellung versendet',
        'subject' => 'Ihre Bestellung :reference ist unterwegs',
        'heading' => 'Ihre Bestellung ist unterwegs',
        'intro' => 'Gute Nachrichten: Die folgenden Artikel Ihrer Bestellung :reference wurden versendet.',
        'tracking' => 'Mit den folgenden Sendungsdaten können Sie Ihre Lieferung verfolgen.',
        'outro' => 'Vielen Dank für Ihren Einkauf.',
    ],

    'order_ready_for_collection' => [
        'label' => 'Abholbereit',
        'subject' => 'Ihre Bestellung :reference ist abholbereit',
        'heading' => 'Ihre Bestellung ist abholbereit',
        'intro' => 'Die folgenden Artikel Ihrer Bestellung :reference liegen zur Abholung bereit.',
        'outro' => 'Bitte halten Sie bei der Abholung Ihre Bestellnummer bereit.',
    ],

    'order_provisioned' => [
        'label' => 'Bestellung bereitgestellt',
        'subject' => 'Ihre Bestellung :reference ist bereit',
        'heading' => 'Ihre digitalen Artikel sind bereit',
        'intro' => 'Die folgenden Artikel Ihrer Bestellung :reference sind jetzt verfügbar.',
        'outro' => 'Vielen Dank für Ihren Einkauf.',
    ],

    'return_received' => [
        'label' => 'Retoure eingegangen',
        'subject' => 'Ihre Retoure zur Bestellung :reference ist eingegangen',
        'heading' => 'Retoure eingegangen',
        'intro' => 'Wir haben die folgenden Artikel Ihrer Bestellung :reference zurückerhalten.',
        'outro' => 'Falls eine Erstattung fällig ist, bestätigen wir diese in einer separaten E-Mail.',
    ],

    'order_cancelled' => [
        'label' => 'Bestellung storniert',
        'subject' => 'Ihre Bestellung :reference wurde storniert',
        'heading' => 'Ihre Bestellung wurde storniert',
        'intro' => 'Ihre Bestellung :reference wurde storniert.',
        'outro' => 'Bei Fragen antworten Sie bitte einfach auf diese E-Mail.',
    ],

    'refund_issued' => [
        'label' => 'Erstattung veranlasst',
        'subject' => 'Erstattung für Bestellung :reference veranlasst',
        'heading' => 'Erstattung veranlasst',
        'intro' => 'Wir haben eine Erstattung für Ihre Bestellung :reference veranlasst.',
        'outro' => 'Je nach Zahlungsanbieter kann es einige Tage dauern, bis die Erstattung angezeigt wird.',
    ],

    'partial_fulfilment_update' => [
        'label' => 'Teillieferung',
        'subject' => 'Neuigkeiten zum Rest Ihrer Bestellung :reference',
        'heading' => 'Neuigkeiten zu Ihrer Bestellung',
        'intro' => 'Ein Teil Ihrer Bestellung :reference wurde bereits versendet. Die übrigen Artikel werden noch vorbereitet.',
        'outstanding' => 'Die folgenden Artikel folgen noch:',
        'outro' => 'Wir bitten um Entschuldigung für die Verzögerung und melden uns, sobald sie unterwegs sind.',
    ],

    'partials' => [
        'greeting' => 'Hallo :name,',
        'greeting_fallback' => 'Hallo,',
        'signoff' => 'Mit freundlichen Grüßen',
        'item' => 'Artikel',
        'quantity' => 'Menge',
        'total' => 'Gesamt',
        'sub_total' => 'Zwischensumme',
        'discount' => 'Rabatt',
        'shipping' => 'Versand',
        'tax' => 'Steuern',
        'order_total' => 'Bestellsumme',
        'carrier' => 'Versanddienstleister',
        'tracking_number' => 'Sendungsnummer',
        'track' => 'Sendung verfolgen',
        'shipping_address' => 'Lieferadresse',
        'billing_address' => 'Rechnungsadresse',
        'payment_status' => 'Zahlungsstatus',
        'collect_from' => 'Abholung bei',
        'reason' => 'Grund',
        'refund_amount' => 'Erstattungsbetrag',
    ],

];
