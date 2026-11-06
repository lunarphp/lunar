<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\Core\Actions\Carts\MergeCart;
use Lunar\Core\Actions\Carts\UpdateCartLine;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Exceptions\NonPurchasableItemException;
use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartAddress;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\TaxClass;
use Lunar\Core\Models\TaxZone;
use Lunar\Tests\Core\Stubs\User as StubUser;
use Lunar\Tests\Core\TestCase;
use Spatie\Activitylog\Models\Activity;

uses(TestCase::class)->group('carts');

uses(RefreshDatabase::class);

/**
 * A calculated cart with one priced line and a persisted snapshot, plus a
 * second priced variant to add.
 *
 * @return array{0: Cart, 1: ProductVariant, 2: ProductVariant}
 */
function snapshotCart(): array
{
    setAuthUserConfig();

    $currency = Currency::factory()->create(['default' => true]);

    $cart = Cart::factory()->create(['currency_id' => $currency->id]);

    $variants = collect([1000, 2000])->map(function (int $amount) use ($currency) {
        $variant = ProductVariant::factory()->inStock(10)->create();

        Price::factory()->create([
            'price' => $amount,
            'min_quantity' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => $variant->getMorphClass(),
            'priceable_id' => $variant->id,
        ]);

        return $variant;
    });

    $cart->lines()->create([
        'purchasable_type' => $variants[0]->getMorphClass(),
        'purchasable_id' => $variants[0]->id,
        'quantity' => 1,
    ]);

    $cart->refresh()->calculate();

    return [$cart, $variants[0], $variants[1]];
}

function rowRevisions(Cart $cart): array
{
    $row = Cart::query()->toBase()->find($cart->id);

    return [(int) $row->revision, $row->calculated_revision === null ? null : (int) $row->calculated_revision];
}

/**
 * snapshotCart() leaves the row at revision 1 (the line creation bumped it)
 * with a snapshot at 1; assert the number of bumps since, and whether a
 * verb's own recalculation has caught the snapshot up.
 */
function expectBumped(Cart $cart, int $times, bool $recalculated): void
{
    [$revision, $calculatedRevision] = rowRevisions($cart);

    expect($revision)->toBe(1 + $times)
        ->and($calculatedRevision)->toBe($recalculated ? 1 + $times : 1);
}

test('a new cart has no snapshot and is not fresh', function () {
    $cart = Cart::factory()->create();

    expect($cart->revision)->toBe(0)
        ->and($cart->calculated_revision)->toBeNull()
        ->and($cart->calculated_at)->toBeNull()
        ->and($cart->totalsAreFresh())->toBeFalse();
});

test('freshness honours the configured ttl', function () {
    [$cart] = snapshotCart();

    expect($cart->totalsAreFresh())->toBeTrue();

    $this->travel(299)->seconds();
    expect($cart->totalsAreFresh())->toBeTrue();

    $this->travel(2)->seconds();
    expect($cart->totalsAreFresh())->toBeFalse();

    Config::set('lunar.cart.totals.ttl', 3600);
    expect($cart->totalsAreFresh())->toBeTrue();

    Config::set('lunar.cart.totals.ttl', 0);
    expect($cart->totalsAreFresh())->toBeFalse();
});

test('invalidateTotals bumps the revision once, quietly, and leaves the snapshot columns readable', function () {
    [$cart] = snapshotCart();

    $saved = 0;
    Cart::saved(function () use (&$saved) {
        $saved++;
    });

    $cart->invalidateTotals();

    expectBumped($cart, 1, false);

    expect($saved)->toBe(0)
        ->and($cart->fresh()->totalsAreFresh())->toBeFalse()
        ->and(Cart::query()->toBase()->find($cart->id)->total)->not->toBeNull();
});

test('adding a line bumps the revision once and the verb recalculates', function () {
    [$cart, , $other] = snapshotCart();

    $cart->add($other, 1, refresh: false);

    expectBumped($cart, 1, false);

    $cart->refresh()->recalculate();

    expectBumped($cart, 1, true);

    expect($cart->totalsAreFresh())->toBeTrue()
        ->and($cart->subTotal->value)->toBe(3000);
});

test('updating a line bumps the revision once', function () {
    [$cart] = snapshotCart();

    $cart->updateLine($cart->lines->first()->id, 3, refresh: false);

    expectBumped($cart, 1, false);
});

test('removing a line bumps the revision once', function () {
    [$cart] = snapshotCart();

    $cart->remove($cart->lines->first()->id, refresh: false);

    expectBumped($cart, 1, false);
});

test('clearing the cart bumps the revision once', function () {
    [$cart] = snapshotCart();

    $cart->clear();

    expectBumped($cart, 1, true);

    expect($cart->total->value)->toBe(0);
});

test('adding an address bumps the revision once', function () {
    [$cart] = snapshotCart();

    $country = Country::factory()->create();

    $cart->addAddress(CartAddress::factory()->make(['country_id' => $country->id])->toArray(), 'shipping', refresh: false);

    expectBumped($cart, 1, false);

    // Replacing the address: the query-builder delete fires nothing, the save
    // fires once.
    $cart->addAddress(CartAddress::factory()->make(['country_id' => $country->id])->toArray(), 'shipping', refresh: false);

    expectBumped($cart, 2, false);
});

test('setting the shipping option bumps the revision once', function () {
    [$cart] = snapshotCart();

    $taxClass = TaxClass::factory()->create();

    $option = new ShippingOption(
        name: 'Basic Delivery',
        description: 'Basic Delivery',
        identifier: 'BASDEL',
        price: new PriceValue(500, $cart->currency),
        taxClass: $taxClass,
    );

    ShippingManifest::addOption($option);

    $cart->addAddress(CartAddress::factory()->make()->toArray(), 'shipping');

    $before = rowRevisions($cart)[0];

    $cart->setShippingOption($option, refresh: false);

    expect(rowRevisions($cart)[0])->toBe($before + 1)
        ->and(rowRevisions($cart)[1])->toBe($before);
});

test('setting the tax zone bumps the revision once', function () {
    [$cart] = snapshotCart();

    $cart->setTaxZone(TaxZone::factory()->create());

    expectBumped($cart, 1, true);
});

test('associating a user bumps the revision once', function () {
    [$cart] = snapshotCart();

    $cart->associate(StubUser::factory()->create(), refresh: false);

    expectBumped($cart, 1, false);
});

test('setting the customer bumps the revision once', function () {
    [$cart] = snapshotCart();

    $cart->setCustomer(Customer::factory()->create());

    expectBumped($cart, 1, true);
});

test('updating a cart attribute outside the snapshot bumps the revision once', function () {
    [$cart] = snapshotCart();

    $cart->update(['coupon_code' => 'TENOFF']);

    expectBumped($cart, 1, false);

    expect($cart->revision)->toBe(2);

    $cart->update(['channel_id' => Channel::factory()->create()->id]);

    expectBumped($cart, 2, false);

    expect($cart->revision)->toBe(3);
});

test('a save that changes nothing outside the snapshot does not bump the revision', function () {
    [$cart] = snapshotCart();

    $cart->touch();
    $cart->save();

    expectBumped($cart, 0, true);
});

test('merging a cart bumps the target revision once per line written', function () {
    [$target, , $other] = snapshotCart();

    $source = Cart::factory()->create(['currency_id' => $target->currency_id]);

    $source->lines()->create([
        'purchasable_type' => $other->getMorphClass(),
        'purchasable_id' => $other->id,
        'quantity' => 1,
    ]);

    app(MergeCart::class)->execute($target, $source->refresh());

    expectBumped($target, 1, false);
});

test('concurrent bumps never collapse into one', function () {
    [$cart] = snapshotCart();

    $a = Cart::query()->find($cart->id);
    $b = Cart::query()->find($cart->id);

    expect($a->revision)->toBe(1)->and($b->revision)->toBe(1);

    $a->invalidateTotals();
    $b->update(['coupon_code' => 'TENOFF']);

    // A PHP-side read-modify-write would leave this at 2.
    expect(rowRevisions($cart)[0])->toBe(3)
        ->and($b->revision)->toBe(3);
});

test('a line saved against a different cart instance still invalidates', function () {
    [$cart] = snapshotCart();

    $line = CartLine::query()->find($cart->lines->first()->id);

    $line->update(['quantity' => 5]);

    expectBumped($cart, 1, false);
});

test('UpdateCartLine runs the purchasable check', function () {
    [$cart] = snapshotCart();

    $line = $cart->lines->first();

    CartLine::query()->whereKey($line->id)->toBase()->update([
        'purchasable_type' => Currency::class,
        'purchasable_id' => $cart->currency_id,
    ]);

    expect(fn () => (new UpdateCartLine)->execute($line->id, 2))
        ->toThrow(NonPurchasableItemException::class);
});

test('the revision and snapshot columns stay out of the activity log', function () {
    [$cart] = snapshotCart();

    activity()->enableLogging();

    $cart->update(['coupon_code' => 'TENOFF']);

    activity()->disableLogging();

    $activity = Activity::query()->where('subject_id', $cart->id)->latest('id')->first();

    expect($activity)->not->toBeNull()
        ->and($activity->properties['attributes'])->toHaveKey('coupon_code')
        ->and($activity->properties['attributes'])->not->toHaveKey('revision')
        ->and($activity->properties['attributes'])->not->toHaveKey('total');
});
