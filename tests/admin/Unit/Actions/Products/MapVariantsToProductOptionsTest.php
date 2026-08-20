<?php

use Lunar\Admin\Actions\Products\MapVariantsToProductOptions;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
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
});

it('does not adopt the first variant for a permutation that matches nothing', function () {

    $optionValues = [
        'Colour' => [
            'Red',
            'Blue',
        ],
        'Size' => [
            'Small',
            'Large',
        ],
    ];

    // A sparse product: only two of the four combinations actually exist.
    $variants = [
        [
            'id' => 1,
            'sku' => 'RED-SMALL',
            'price' => 10,
            'stock' => 1,
            'values' => [
                'Colour' => 'Red',
                'Size' => 'Small',
            ],
        ],
        [
            'id' => 2,
            'sku' => 'BLUE-LARGE',
            'price' => 20,
            'stock' => 2,
            'values' => [
                'Colour' => 'Blue',
                'Size' => 'Large',
            ],
        ],
    ];

    $result = MapVariantsToProductOptions::map($optionValues, $variants);

    expect($result)->toHaveCount(4);

    $blueSmall = collect($result)->first(
        fn ($row) => $row['values'] === ['Colour' => 'Blue', 'Size' => 'Small']
    );

    // "Blue/Small" matches neither variant, so it must not inherit the id, sku,
    // price or stock of whichever variant happens to be first in the list.
    expect($blueSmall['copied_id'])->toBeNull()
        ->and($blueSmall['variant_id'])->toBeNull()
        ->and($blueSmall['sku'])->toBeNull()
        ->and($blueSmall['price'])->toBe(0)
        ->and($blueSmall['stock'])->toBe(0);
});
