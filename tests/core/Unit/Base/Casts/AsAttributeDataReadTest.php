<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Product;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

/**
 * A json column accepts any JSON value, and rows arrive from imports, from other
 * Lunar versions and from Lunar's own failed writes. None of them should stop a
 * record being hydrated.
 */
test('can load a record whose attribute data is not an attribute set', function (string $stored) {
    $product = Product::factory()->create([
        'attribute_data' => collect([
            'name' => new TranslatedText(['en' => 'A name']),
        ]),
    ]);

    DB::table('lunar_products')->where('id', $product->id)->update(['attribute_data' => $stored]);

    $loaded = Product::find($product->id);

    expect($loaded->attribute_data)->toHaveCount(0);
})->with([
    'json null' => 'null',
    'zero, as a failed encode leaves it' => '0',
    'a json string' => '"abc"',
    'a number' => '123',
    'malformed json' => '{oops',
]);

test('can still load attribute data that is an attribute set', function () {
    $product = Product::factory()->create([
        'attribute_data' => collect([
            'name' => new TranslatedText(['en' => 'A name']),
        ]),
    ]);

    expect(Product::find($product->id)->translateAttribute('name'))->toEqual('A name');
});
