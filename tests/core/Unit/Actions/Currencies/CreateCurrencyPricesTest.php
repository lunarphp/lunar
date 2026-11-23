<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Lunar\Core\Contracts\Actions\Currencies\CreatesCurrencyPrices;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class)->group('cross-db');

uses(RefreshDatabase::class);

test('copies the base currency prices with fresh public ids', function () {
    $base = Currency::factory()->create(['default' => true, 'exchange_rate' => 1]);

    $variants = ProductVariant::factory()->count(3)->create();

    foreach ($variants as $index => $variant) {
        Price::factory()->create([
            'currency_id' => $base->id,
            'priceable_type' => $variant->getMorphClass(),
            'priceable_id' => $variant->id,
            'price' => 1000 + $index,
            'list_price' => $index === 0 ? null : 2000 + $index,
            'min_quantity' => $index + 1,
        ]);
    }

    // Created quietly so the observer's job does not run the copy first.
    $incoming = Currency::factory()->createQuietly(['default' => false, 'exchange_rate' => 1.5]);

    app(CreatesCurrencyPrices::class)->execute($incoming, $base);

    $copied = Price::query()->where('currency_id', $incoming->id)->orderBy('priceable_id')->get();

    expect($copied)->toHaveCount(3);

    $basePublicIds = Price::query()->where('currency_id', $base->id)->pluck('public_id');

    expect($copied->pluck('public_id')->filter()->unique())->toHaveCount(3)
        ->and($copied->pluck('public_id')->intersect($basePublicIds))->toBeEmpty();

    $copied->each(function (Price $price) {
        expect(Str::isUlid($price->public_id))->toBeTrue();
    });

    expect($copied->map(fn (Price $price) => [
        $price->priceable_id,
        $price->price,
        $price->list_price,
        $price->min_quantity,
    ])->all())->toEqual($variants->values()->map(fn (ProductVariant $variant, int $index) => [
        $variant->id,
        (int) round((1000 + $index) * 1.5),
        $index === 0 ? null : (int) round((2000 + $index) * 1.5),
        $index + 1,
    ])->all());
});

test('creating a currency copies the default currency prices', function () {
    $base = Currency::factory()->create(['default' => true, 'exchange_rate' => 1]);

    $variant = ProductVariant::factory()->create();

    Price::factory()->create([
        'currency_id' => $base->id,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'price' => 1000,
    ]);

    $incoming = Currency::factory()->create(['default' => false, 'exchange_rate' => 2]);

    $price = Price::query()->where('currency_id', $incoming->id)->sole();

    expect($price->public_id)->not->toBeEmpty()
        ->and($price->price)->toBe(2000);
});
