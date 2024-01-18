<?php

use Lunar\Admin\Support\Actions\Products\MapVariantsToProductOptions;

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('support.actions');

it('can map variants given a set of option values', function () {

    $optionValues = [
        'Shoe Size' => [
            'UK 5',
            'UK 10',
            'UK 15',
        ],
    ];

    $variants = [
        [
            'id' => 1,
            'sku' => 'ABC',
            'values' => [
                'Shoe Size' => 'UK 5',
            ],
        ],
        [
            'id' => 2,
            'sku' => 'DEF',
            'values' => [
                'Shoe Size' => 'UK 10',
            ],
        ],
        [
            'id' => 3,
            'sku' => 'GHI',
            'values' => [
                'Shoe Size' => 'UK 15',
            ],
        ],
    ];
    $result = MapVariantsToProductOptions::map($optionValues, $variants);
    dd($result);
});
