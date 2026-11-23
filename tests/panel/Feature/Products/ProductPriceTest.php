<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Language::factory()->create(['default' => true, 'code' => 'en']);

    $this->gbp = Currency::factory()->create(['code' => 'GBP', 'default' => true, 'decimal_places' => 2, 'enabled' => true]);
    $this->jpy = Currency::factory()->create(['code' => 'JPY', 'default' => false, 'decimal_places' => 0, 'enabled' => true]);
    // Created before any price exists: CreateCurrencyPrices copies rows
    // without a public_id, so it fails once prices are present.
    $this->eur = Currency::factory()->create(['code' => 'EUR', 'default' => false, 'enabled' => false]);
    $this->group = CustomerGroup::factory()->create();

    $this->product = Product::factory()->create(['name' => collect(['en' => 'Widget'])]);
    $this->variant = ProductVariant::factory()->create(['product_id' => $this->product->id]);
    $this->variant->prices()->delete();

    $this->variant->prices()->create(['currency_id' => $this->gbp->id, 'price' => 1000, 'min_quantity' => 1]);
});

/** @return array<string, array{int, ?int}> tuple to [price, list_price] */
function priceRows(ProductVariant $variant): array
{
    return Price::query()->where('priceable_type', $variant->getMorphClass())->where('priceable_id', $variant->id)->with('currency')->get()
        ->mapWithKeys(fn (Price $price) => [
            $price->currency->code.':'.($price->customer_group_id ?? 0).':'.$price->min_quantity => [$price->price, $price->list_price],
        ])
        ->sortKeys()
        ->all();
}

function commitVariant(array $data, array $extra = [])
{
    return test()->postJson(route('panel.products.variants.draft.commit', [test()->product, test()->variant]), [
        'data' => $data,
        'rebase' => [],
        ...$extra,
    ]);
}

it('seeds per-currency price lists in minor units on the variant page', function () {
    $this->variant->prices()->create(['currency_id' => $this->gbp->id, 'price' => 900, 'min_quantity' => 10]);

    $this->get(route('panel.products.variants.edit', [$this->product, $this->variant]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('formSliceValues.price:GBP', [
                ['customer_group_id' => null, 'min_quantity' => 1, 'price' => 1000, 'list_price' => null],
                ['customer_group_id' => null, 'min_quantity' => 10, 'price' => 900, 'list_price' => null],
            ])
            ->where('formSliceValues.price:JPY', [])
        );
});

it('commits added, changed and removed tiers and nothing before Save', function () {
    $this->variant->prices()->create(['currency_id' => $this->gbp->id, 'price' => 900, 'min_quantity' => 10]);

    $data = [
        'price:GBP' => [
            ['customer_group_id' => null, 'min_quantity' => 1, 'price' => '1250', 'list_price' => '1500'],
            ['customer_group_id' => (string) $this->group->id, 'min_quantity' => '1', 'price' => 800, 'list_price' => null],
        ],
        'price:JPY' => [
            ['customer_group_id' => null, 'min_quantity' => 1, 'price' => 1500, 'list_price' => null],
        ],
    ];

    $this->patchJson(route('panel.products.variants.draft.update', [$this->product, $this->variant]), ['data' => $data])
        ->assertOk();

    // Drafting alone changes nothing.
    expect(priceRows($this->variant))->toBe(['GBP:0:1' => [1000, null], 'GBP:0:10' => [900, null]]);

    commitVariant($data)->assertOk();

    expect(priceRows($this->variant))->toBe([
        'GBP:0:1' => [1250, 1500],
        "GBP:{$this->group->id}:1" => [800, null],
        'JPY:0:1' => [1500, null],
    ]);
});

it('refuses removing a stored base price and duplicate rows, on the offending field', function () {
    commitVariant(['price:GBP' => [
        ['customer_group_id' => null, 'min_quantity' => 5, 'price' => 900],
    ]])->assertJsonValidationErrors(['price:GBP' => __('panel::pricing.error_base_required')]);

    commitVariant(['price:GBP' => [
        ['customer_group_id' => null, 'min_quantity' => 1, 'price' => 1000],
        ['customer_group_id' => null, 'min_quantity' => 1, 'price' => 900],
    ]])->assertJsonValidationErrors(['price:GBP' => __('panel::pricing.error_duplicate_row')]);

    commitVariant(['price:GBP' => [
        ['customer_group_id' => null, 'min_quantity' => 1, 'price' => 1000],
        ['customer_group_id' => null, 'min_quantity' => 0, 'price' => -1],
    ]])->assertJsonValidationErrors(['price:GBP.1.price', 'price:GBP.1.min_quantity']);

    expect(priceRows($this->variant))->toBe(['GBP:0:1' => [1000, null]]);
});

it('keeps prices in a disabled currency', function () {
    $this->variant->prices()->create(['currency_id' => $this->eur->id, 'price' => 700, 'min_quantity' => 1]);

    commitVariant(['price:GBP' => [
        ['customer_group_id' => null, 'min_quantity' => 1, 'price' => 1100],
    ]])->assertOk();

    expect(priceRows($this->variant))->toBe(['EUR:0:1' => [700, null], 'GBP:0:1' => [1100, null]]);
});

it('conflicts only on the currency a colleague repriced', function () {
    $mine = [
        'price:GBP' => [['customer_group_id' => null, 'min_quantity' => 1, 'price' => 1200, 'list_price' => null]],
        'price:JPY' => [['customer_group_id' => null, 'min_quantity' => 1, 'price' => 1500, 'list_price' => null]],
    ];

    $this->patchJson(route('panel.products.variants.draft.update', [$this->product, $this->variant]), ['data' => $mine])
        ->assertOk();

    // A colleague (or a bundle reprice) changes the GBP base price meanwhile.
    $this->variant->syncPrices([['currency_id' => $this->gbp->id, 'customer_group_id' => null, 'min_quantity' => 1, 'price' => 1111]]);

    $response = commitVariant($mine)->assertConflict();

    expect(collect($response->json('conflicts'))->pluck('key')->all())->toBe(['price:GBP'])
        ->and(priceRows($this->variant))->toBe(['GBP:0:1' => [1111, null]]);
});

it('drafts the sole variant prices on the product page under variant:price', function () {
    $this->get(route('panel.products.edit', $this->product))
        ->assertInertia(fn (Assert $page) => $page
            ->where('formSliceValues.variant:price:GBP.0.price', 1000)
        );

    $data = ['variant:price:GBP' => [
        ['customer_group_id' => null, 'min_quantity' => 1, 'price' => 1300, 'list_price' => null],
        ['customer_group_id' => null, 'min_quantity' => 3, 'price' => 1200, 'list_price' => null],
    ]];

    $this->postJson(route('panel.products.draft.commit', $this->product), ['data' => $data, 'rebase' => []])
        ->assertOk();

    expect(priceRows($this->variant))->toBe(['GBP:0:1' => [1300, null], 'GBP:0:3' => [1200, null]]);
});

it('refuses product-page prices once the product has several variants', function () {
    ProductVariant::factory()->create(['product_id' => $this->product->id]);

    $this->postJson(route('panel.products.draft.commit', $this->product), [
        'data' => ['variant:price:GBP' => [['customer_group_id' => null, 'min_quantity' => 1, 'price' => 1]]],
        'rebase' => [],
    ])->assertJsonValidationErrors('variant:price:GBP');

    expect(priceRows($this->variant))->toBe(['GBP:0:1' => [1000, null]]);
});
