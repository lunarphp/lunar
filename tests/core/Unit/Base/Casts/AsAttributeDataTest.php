<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Product;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('can not overwrite attribute data with an unencodable value', function () {
    $product = Product::factory()->create([
        'attribute_data' => collect([
            'name' => new TranslatedText(['en' => 'A name worth keeping']),
        ]),
    ]);

    $before = DB::table('lunar_products')->where('id', $product->id)->value('attribute_data');

    // A single 0xE4 byte - "a" with an umlaut, as Latin-1 writes it. Any feed
    // that is not UTF-8 produces these. The cast runs on assignment, so this
    // never reaches the database at all.
    expect(function () use ($product) {
        $product->attribute_data = collect([
            'name' => new TranslatedText(['en' => "Sh\xE4mpoo"]),
        ]);

        $product->save();
    })->toThrow(JsonException::class);

    $after = DB::table('lunar_products')->where('id', $product->id)->value('attribute_data');

    // The point is not that it failed, but that it failed without taking the
    // existing attributes with it.
    expect($after)->toEqual($before);

    expect(Product::find($product->id)->translateAttribute('name'))
        ->toEqual('A name worth keeping');
});

test('can store attribute data containing multibyte characters', function () {
    $product = Product::factory()->create([
        'attribute_data' => collect([
            'name' => new TranslatedText(['en' => 'Crème brûlée · 日本語 · emoji 🧴']),
        ]),
    ]);

    expect(Product::find($product->id)->translateAttribute('name'))
        ->toEqual('Crème brûlée · 日本語 · emoji 🧴');
});
