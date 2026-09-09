<?php

return [

    'order_update' => [
        'label' => 'Bestelupdate',
        'subject' => 'Een update over je bestelling :reference',
        'greeting' => 'Hallo,',
        'intro' => 'We willen je graag op de hoogte brengen van je bestelling :reference.',
        'outro' => 'Bedankt voor je bestelling.',
    ],

    'order_confirmation' => [
        'label' => 'Orderbevestiging',
        'subject' => 'Je bestelling :reference is ontvangen',
        'heading' => 'Bedankt voor je bestelling',
        'intro' => 'We hebben je bestelling :reference, geplaatst op :date, ontvangen. Hieronder vind je een overzicht van je bestelling.',
        'outro' => 'We laten het je weten zodra je bestelling onderweg is.',
    ],

    'payment_received' => [
        'label' => 'Betaling ontvangen',
        'subject' => 'Betaling ontvangen voor bestelling :reference',
        'heading' => 'Betaling ontvangen',
        'intro' => 'We hebben je betaling voor bestelling :reference ontvangen.',
        'outro' => 'Bedankt. We nemen contact op zodra je bestelling onderweg is.',
    ],

    'order_shipped' => [
        'label' => 'Bestelling verzonden',
        'subject' => 'Je bestelling :reference is onderweg',
        'heading' => 'Je bestelling is onderweg',
        'intro' => 'Goed nieuws: de volgende artikelen uit je bestelling :reference zijn verzonden.',
        'tracking' => 'Je kunt je bezorging volgen met de onderstaande track-and-tracegegevens.',
        'outro' => 'Bedankt voor je bestelling.',
    ],

    'order_ready_for_collection' => [
        'label' => 'Klaar om af te halen',
        'subject' => 'Je bestelling :reference staat klaar om af te halen',
        'heading' => 'Je bestelling staat klaar om af te halen',
        'intro' => 'De volgende artikelen uit je bestelling :reference staan klaar om af te halen.',
        'outro' => 'Neem je bestelnummer mee wanneer je de bestelling komt afhalen.',
    ],

    'order_provisioned' => [
        'label' => 'Bestelling beschikbaar gesteld',
        'subject' => 'Je bestelling :reference is klaar',
        'heading' => 'Je digitale artikelen zijn klaar',
        'intro' => 'De volgende artikelen uit je bestelling :reference zijn nu beschikbaar.',
        'outro' => 'Bedankt voor je bestelling.',
    ],

    'return_received' => [
        'label' => 'Retour ontvangen',
        'subject' => 'We hebben je retour voor bestelling :reference ontvangen',
        'heading' => 'Retour ontvangen',
        'intro' => 'We hebben de volgende artikelen uit je bestelling :reference retour ontvangen.',
        'outro' => 'Als je recht hebt op een terugbetaling, bevestigen we dat in een aparte e-mail.',
    ],

    'order_cancelled' => [
        'label' => 'Bestelling geannuleerd',
        'subject' => 'Je bestelling :reference is geannuleerd',
        'heading' => 'Je bestelling is geannuleerd',
        'intro' => 'Je bestelling :reference is geannuleerd.',
        'outro' => 'Heb je vragen? Beantwoord dan gewoon deze e-mail.',
    ],

    'refund_issued' => [
        'label' => 'Terugbetaling uitgevoerd',
        'subject' => 'Er is een terugbetaling uitgevoerd voor bestelling :reference',
        'heading' => 'Terugbetaling uitgevoerd',
        'intro' => 'We hebben een terugbetaling uitgevoerd voor je bestelling :reference.',
        'outro' => 'Afhankelijk van je betaalprovider kan het enkele dagen duren voordat de terugbetaling zichtbaar is.',
    ],

    'partial_fulfilment_update' => [
        'label' => 'Deellevering',
        'subject' => 'Een update over de rest van je bestelling :reference',
        'heading' => 'Een update over je bestelling',
        'intro' => 'Een deel van je bestelling :reference is al verzonden. De overige artikelen worden nog voorbereid.',
        'outstanding' => 'De volgende artikelen volgen nog:',
        'outro' => 'Onze excuses voor de vertraging. We laten het je weten zodra ze onderweg zijn.',
    ],

    'partials' => [
        'greeting' => 'Hoi :name,',
        'greeting_fallback' => 'Hallo,',
        'signoff' => 'Met vriendelijke groet,',
        'item' => 'Artikel',
        'quantity' => 'Aantal',
        'total' => 'Totaal',
        'sub_total' => 'Subtotaal',
        'discount' => 'Korting',
        'shipping' => 'Verzending',
        'tax' => 'Btw',
        'order_total' => 'Totaalbedrag',
        'carrier' => 'Vervoerder',
        'tracking_number' => 'Track-and-tracecode',
        'track' => 'Volg je pakket',
        'shipping_address' => 'Verzendadres',
        'billing_address' => 'Factuuradres',
        'payment_status' => 'Betaalstatus',
        'collect_from' => 'Afhalen bij',
        'reason' => 'Reden',
        'refund_amount' => 'Terugbetaald bedrag',
    ],

];
