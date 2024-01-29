<?php

use Lunar\Admin\Actions\Products\MapVariantsToProductOptions;

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('support.actions');

it('can map variants given one set of option values', function () {

    $optionValues = [
        'Shoe Size' => [
            'UK-5',
            'UK-10',
            'UK-15',
        ],
    ];

    $variants = [
        [
            'id' => 1,
            'sku' => 'ABC',
            'values' => [
                'Shoe Size' => 'UK-5',
            ],
        ],
        [
            'id' => 2,
            'sku' => 'DEF',
            'values' => [
                'Shoe Size' => 'UK-10',
            ],
        ],
        [
            'id' => 3,
            'sku' => 'GHI',
            'values' => [
                'Shoe Size' => 'UK-15',
            ],
        ],
    ];

    $result = MapVariantsToProductOptions::map($optionValues, $variants);

    expect($result[0]['sku'])->toBe('ABC');
    expect($result[1]['sku'])->toBe('DEF');
    expect($result[2]['sku'])->toBe('GHI');
});

it('can map variants given three sets of option values', function () {

    $optionValues = [
        'Size' => [
            'Small',
            'Medium',
        ],
        'Colour' => [
            'Blue',
            'Black',
        ],
        'Material' => [
            'Black',
        ],
    ];

    $variants = [
        [
            'id' => 1,
            'sku' => 'SMBLK',
            'values' => [
                'Size' => 'Small',
                'Colour' => 'Black',
            ],
        ],
    ];

    $result = MapVariantsToProductOptions::map($optionValues, $variants);

    expect($result)->toHaveCount(4);
})->group('momo');
