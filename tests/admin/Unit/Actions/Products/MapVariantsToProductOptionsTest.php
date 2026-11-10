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

it('does not assign the first variant sku to unmatched sparse permutations', function () {
    $optionValues = [
        'Size' => [
            'XS',
            'L',
        ],
        'Colour' => [
            'Navy',
            'White',
        ],
        'Length' => [
            'Long',
            'Short',
        ],
        'Fit' => [
            'Slim',
        ],
    ];

    $variants = [
        [
            'id' => 1,
            'sku' => 'TEE-L-NAVY-SHORT',
            'price' => 0,
            'stock' => 0,
            'values' => [
                'Size' => 'L',
                'Colour' => 'Navy',
                'Length' => 'Short',
                'Fit' => 'Slim',
            ],
        ],
        [
            'id' => 2,
            'sku' => 'TEE-XS-WHITE-LONG',
            'price' => 0,
            'stock' => 0,
            'values' => [
                'Size' => 'XS',
                'Colour' => 'White',
                'Length' => 'Long',
                'Fit' => 'Slim',
            ],
        ],
    ];

    $result = MapVariantsToProductOptions::map($optionValues, $variants, fillMissing: false);

    expect($result)->toHaveCount(2);

    $mappedBySku = collect($result)->keyBy('sku');

    expect($mappedBySku['TEE-L-NAVY-SHORT']['values'])->toBe([
        'Size' => 'L',
        'Colour' => 'Navy',
        'Length' => 'Short',
        'Fit' => 'Slim',
    ])->and($mappedBySku['TEE-XS-WHITE-LONG']['values'])->toBe([
        'Size' => 'XS',
        'Colour' => 'White',
        'Length' => 'Long',
        'Fit' => 'Slim',
    ]);

    // First cartesian permutation is XS/Navy/Long — must not steal the first variant SKU.
    expect(
        collect($result)->contains(fn (array $row) => $row['sku'] === 'TEE-L-NAVY-SHORT'
            && $row['values']['Size'] === 'XS')
    )->toBeFalse();
});

it('keeps unmatched permutations without binding the first variant when fillMissing is enabled', function () {
    $optionValues = [
        'Size' => [
            'Small',
            'Large',
        ],
        'Colour' => [
            'Red',
            'Blue',
        ],
    ];

    $variants = [
        [
            'id' => 10,
            'sku' => 'SMALL-RED',
            'price' => 1,
            'stock' => 5,
            'values' => [
                'Size' => 'Small',
                'Colour' => 'Red',
            ],
        ],
    ];

    $result = MapVariantsToProductOptions::map($optionValues, $variants, fillMissing: true);

    expect($result)->toHaveCount(4);

    $exact = collect($result)->first(
        fn (array $row) => $row['values'] === ['Size' => 'Small', 'Colour' => 'Red']
    );

    expect($exact['sku'])->toBe('SMALL-RED')
        ->and($exact['variant_id'])->toBe(10)
        ->and($exact['copied_id'])->toBeNull();

    $unmatched = collect($result)
        ->reject(fn (array $row) => $row['variant_id'] === 10);

    expect($unmatched->pluck('sku')->all())->each->toBeNull();
    expect($unmatched->pluck('copied_id')->unique()->all())->toBe([10]);
});
