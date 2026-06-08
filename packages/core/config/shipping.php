<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Carriers
    |--------------------------------------------------------------------------
    |
    | The shipping carriers available when recording fulfilment tracking. Each
    | carrier defines a tracking URL template (the "{tracking_number}"
    | placeholder is substituted in), the services it offers (used to populate
    | the shipping method options) and an optional tracking number pattern used
    | to validate the entered reference. Carriers needing custom logic can
    | implement Lunar\Core\Contracts\ShippingCarrier and be registered with the
    | CarrierManifest instead.
    |
    */
    'carriers' => [

        'royal-mail' => [
            'name' => 'Royal Mail',
            'tracking_url' => 'https://www.royalmail.com/track-your-item#/tracking-results/{tracking_number}',
            'services' => [
                'Tracked 24',
                'Tracked 48',
                'Special Delivery Guaranteed',
                'International Tracked',
            ],
        ],

        'dpd' => [
            'name' => 'DPD',
            'tracking_url' => 'https://track.dpd.co.uk/parcels/{tracking_number}',
            'services' => [
                'Next Day',
                'Two Day',
                'Classic (Europe)',
            ],
        ],

        'ups' => [
            'name' => 'UPS',
            'tracking_url' => 'https://www.ups.com/track?tracknum={tracking_number}',
            'services' => [
                'Standard',
                'Express',
                'Express Saver',
            ],
        ],

        'fedex' => [
            'name' => 'FedEx',
            'tracking_url' => 'https://www.fedex.com/fedextrack/?trknbr={tracking_number}',
            'services' => [
                'Priority',
                'Economy',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Measurements
    |--------------------------------------------------------------------------
    |
    | Define the available measurements for shipping dimensions.
    | Each unit has a format string and a conversion factor relative to the base unit (factor 1.0).
    |
    */
    'measurements' => [

        'length' => [

            'm' => [
                'format' => '1,0.000 m',
                'unit' => 1.00,
            ],

            'mm' => [
                'format' => '1,0.000 mm',
                'unit' => 1000,
            ],

            'cm' => [
                'format' => '1!0 cm',
                'unit' => 100,
            ],

            'ft' => [
                'format' => '1,0.00 ft.',
                'unit' => 3.28084,
            ],

            'in' => [
                'format' => '1,0.00 in.',
                'unit' => 39.3701,
            ],

        ],

        'area' => [

            'sqm' => [
                'format' => '1,00.00 sq m',
                'unit' => 1,
            ],

        ],

        'weight' => [

            'kg' => [
                'format' => '1,0.00 kg',
                'unit' => 1.00,
            ],

            'g' => [
                'format' => '1,0.00 g',
                'unit' => 1000.00,
            ],

            'lbs' => [
                'format' => '1,0.00 lbs',
                'unit' => 2.20462,
            ],

        ],

        'volume' => [

            'l' => [
                'format' => '1,00.00l',
                'unit' => 1,
            ],

            'ml' => [
                'format' => '1,00.000ml',
                'unit' => 1000,
            ],

            'gal' => [
                'format' => '1,00.000gal',
                'unit' => 0.264172,
            ],

            'floz' => [
                'format' => '1,00.000Fl oz.',
                'unit' => 33.814,
            ],

        ],

    ],

];
