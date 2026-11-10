<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Lunar\Core\Contracts\Actions\Carts\PersistsCartTotals;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class)->group('carts', 'cross-db');

uses(RefreshDatabase::class);

function persistableCart(): Cart
{
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create(['currency_id' => $currency->id]);

    $variant = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 1500,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
        'quantity' => 2,
    ]);

    return $cart->refresh();
}

test('it does nothing for a cart that has not been calculated', function () {
    $cart = persistableCart();

    expect(app(PersistsCartTotals::class)->execute($cart))->toBeFalse()
        ->and($cart->fresh()->calculated_revision)->toBeNull();
});

test('it writes the snapshot at the revision the cart was loaded with', function () {
    $cart = persistableCart()->calculate();

    // calculate() has already persisted once; wipe the row to isolate the action.
    Cart::query()->whereKey($cart->id)->toBase()->update(['calculated_revision' => null, 'calculated_at' => null, 'total' => null]);

    expect(app(PersistsCartTotals::class)->execute($cart))->toBeTrue();

    $row = Cart::query()->toBase()->find($cart->id);

    expect((int) $row->calculated_revision)->toBe($cart->revision)
        ->and((int) $row->total)->toBe($cart->total->value)
        ->and($row->calculated_at)->not->toBeNull()
        ->and($cart->calculated_revision)->toBe($cart->revision)
        ->and($cart->getAttribute('total'))->toBe($cart->total->value)
        ->and($cart->isDirty())->toBeFalse();
});

test('it skips the write when the cart changed between load and persist', function () {
    $cart = persistableCart()->calculate();

    $loadedAt = $cart->revision;
    $total = $cart->total->value;

    Cart::query()->whereKey($cart->id)->toBase()->update([
        'revision' => $loadedAt + 1,
        'calculated_revision' => null,
        'total' => null,
    ]);

    expect(app(PersistsCartTotals::class)->execute($cart))->toBeFalse();

    $row = Cart::query()->toBase()->find($cart->id);

    expect($row->calculated_revision)->toBeNull()
        ->and($row->total)->toBeNull()
        ->and((int) $row->revision)->toBe($loadedAt + 1)
        ->and($cart->total->value)->toBe($total)
        ->and($cart->isCalculated())->toBeTrue()
        ->and($cart->fresh()->totalsAreFresh())->toBeFalse();
});

test('it does not bump updated_at or fire model events', function () {
    $cart = persistableCart();

    $this->travel(-1)->hours();
    $cart->touch();
    $this->travelBack();

    $updatedAt = $cart->fresh()->updated_at;

    $saved = 0;
    Cart::saved(function () use (&$saved) {
        $saved++;
    });

    $cart->refresh()->calculate();

    expect($cart->fresh()->updated_at->equalTo($updatedAt))->toBeTrue()
        ->and($saved)->toBe(0)
        ->and($cart->fresh()->calculated_revision)->toBe($cart->revision);
});

test('it clears a stored breakdown the calculation no longer produces', function () {
    $cart = persistableCart()->calculate();

    Cart::query()->whereKey($cart->id)->toBase()->update(['shipping_breakdown' => '{"items":[{"name":"Old","identifier":"OLD","price":500}]}']);

    $cart->shippingBreakdown = null;

    expect(app(PersistsCartTotals::class)->execute($cart))->toBeTrue()
        ->and(Cart::query()->toBase()->find($cart->id)->shipping_breakdown)->toBeNull();
});

test('it does not rewrite lines whose stored totals are unchanged', function () {
    $cart = persistableCart()->calculate();

    $lines = (new CartLine)->getTable();
    $writes = 0;

    DB::listen(function ($query) use (&$writes, $lines) {
        if (preg_match("/^update\\s+[`\"]?{$lines}[`\"]?\\s/i", $query->sql)) {
            $writes++;
        }
    });

    $cart->recalculate();

    expect($writes)->toBe(0);
});

test('it does not rewrite a line whose stored JSON the database has reformatted', function () {
    $cart = persistableCart()->calculate();
    $line = $cart->lines->first();

    // Postgres jsonb and MySQL json hand JSON back reformatted: spacing and key order change.
    $stored = json_decode(CartLine::query()->toBase()->find($line->id)->tax_breakdown, true);
    $reformatted = json_encode(array_map(fn ($amount) => is_array($amount) ? array_reverse($amount, true) : $amount, $stored), JSON_PRETTY_PRINT);

    CartLine::query()->whereKey($line->id)->toBase()->update(['tax_breakdown' => $reformatted]);
    $line->setRawAttributes(array_merge($line->getAttributes(), ['tax_breakdown' => $reformatted]), true);

    $lines = (new CartLine)->getTable();
    $writes = 0;

    DB::listen(function ($query) use (&$writes, $lines) {
        if (preg_match("/^update\\s+[`\"]?{$lines}[`\"]?\\s/i", $query->sql)) {
            $writes++;
        }
    });

    $cart->recalculate();

    expect($writes)->toBe(0);
});
