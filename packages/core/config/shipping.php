<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Measurements
    |--------------------------------------------------------------------------
    |
    | You can use any measurements available at
    | https://github.com/cartalyst/converter/edit/master/src/config/config.php
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
                'unit' => 0.453592,
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
