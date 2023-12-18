<?php

uses(\Lunar\Tests\TestCase::class);
use Lunar\Actions\Taxes\GetTaxZonePostcode;
use Lunar\Models\TaxZonePostcode;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can match exact postcode', function () {
    $uk = TaxZonePostcode::factory()->create([
        'postcode' => 'SW1A 0AA',
    ]);

    TaxZonePostcode::factory()->create([
        'postcode' => 'SW*',
    ]);

    $postcode = app(GetTaxZonePostcode::class)->execute('SW1A 0AA');

    expect($postcode->id)->toEqual($uk->id);
});

test('can match using wildcards', function () {
    $postcodes = [
        // UK
        [
            'exact' => 'SW1 1TX',
            'wildcard' => 'SW*',
            'wildcard_tests' => [
                'SW',
                'SW2',
                'SW3 3TT',
            ],
        ],
        // US
        [
            'exact' => '90210',
            'wildcard' => '90*',
            'wildcard_tests' => [
                '90',
                '902',
                '9021',
            ],
        ],
        // Canada
        [
            'exact' => 'A9A-9A9',
            'wildcard' => 'A9A*',
            'wildcard_tests' => [
                'A9A-8A8',
                'A9A',
                'A9A-7',
            ],
        ],
        // Costa Rica
        [
            'exact' => '999-99',
            'wildcard' => '999*',
            'wildcard_tests' => [
                '999-98',
                '999',
            ],
        ],
        // Argentina
        [
            'exact' => 'A9999AAA',
            'wildcard' => 'A9999*',
            'wildcard_tests' => [
                'A9999BBB',
                'A9999AAB',
            ],
        ],
    ];

    foreach ($postcodes as $postcode) {
        $exact = TaxZonePostcode::factory()->create([
            'postcode' => $postcode['exact'],
        ]);

        $wildcard = TaxZonePostcode::factory()->create([
            'postcode' => $postcode['wildcard'],
        ]);

        $resultA = app(GetTaxZonePostcode::class)->execute($postcode['exact']);
        expect($resultA->id)->toEqual($exact->id);

        foreach ($postcode['wildcard_tests'] as $test) {
            $resultB = app(GetTaxZonePostcode::class)->execute($test);
            expect($resultB->id)->toEqual($wildcard->id);
        }
    }
});
