<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    $this->usd = Currency::factory()->create(['default' => true, 'code' => 'USD', 'decimal_places' => 2]);
    $this->gbp = Currency::factory()->create(['default' => false, 'code' => 'GBP', 'decimal_places' => 2]);
    $this->group = CustomerGroup::factory()->create();
    $this->variant = ProductVariant::factory()->create();
    $this->variant->prices()->delete();
});

test('keeps matching rows, creates new combinations and deletes the rest', function () {
    $base = $this->variant->prices()->create(['currency_id' => $this->usd->id, 'price' => 1000, 'min_quantity' => 1]);
    $tier = $this->variant->prices()->create(['currency_id' => $this->usd->id, 'price' => 900, 'min_quantity' => 10]);

    $this->variant->syncPrices([
        ['currency_id' => $this->usd->id, 'customer_group_id' => null, 'min_quantity' => 1, 'price' => 1250, 'list_price' => 1500],
        ['currency_id' => $this->gbp->id, 'customer_group_id' => null, 'min_quantity' => 1, 'price' => 1100],
        ['currency_id' => $this->usd->id, 'customer_group_id' => $this->group->id, 'min_quantity' => 5, 'price' => 800],
    ]);

    $rows = Price::query()->where('priceable_id', $this->variant->id)->get()
        ->mapWithKeys(fn (Price $price) => [
            $price->currency_id.':'.($price->customer_group_id ?? 0).':'.$price->min_quantity => [$price->id, $price->price, $price->list_price],
        ]);

    expect($rows)->toHaveCount(3)
        ->and($rows[$this->usd->id.':0:1'])->toBe([$base->id, 1250, 1500])
        ->and($rows[$this->gbp->id.':0:1'][1])->toBe(1100)
        ->and($rows[$this->usd->id.':'.$this->group->id.':5'][1])->toBe(800)
        ->and(Price::find($tier->id))->toBeNull();
});

test('an empty set removes every row', function () {
    $this->variant->prices()->create(['currency_id' => $this->usd->id, 'price' => 1000, 'min_quantity' => 1]);

    $this->variant->syncPrices([]);

    expect($this->variant->prices()->count())->toBe(0);
});
