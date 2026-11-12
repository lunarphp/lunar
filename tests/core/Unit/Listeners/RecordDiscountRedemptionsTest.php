<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Lunar\Core\Actions\Carts\CreateOrder;
use Lunar\Core\DiscountTypes\FixedAmountOff;
use Lunar\Core\Enums\DiscountLimit;
use Lunar\Core\Events\Discounts\DiscountLimitExceeded;
use Lunar\Core\Events\Orders\OrderPlaced;
use Lunar\Core\Facades\Discounts;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartAddress;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Coupon;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\TaxClass;
use Lunar\Tests\Core\Stubs\User;
use Lunar\Tests\Core\TestCase;
use Spatie\Activitylog\Models\Activity;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    TaxClass::factory()->create(['default' => true]);

    $this->customerGroup = CustomerGroup::factory()->create(['default' => true]);
    $this->channel = Channel::factory()->create(['default' => true]);
    $this->currency = Currency::factory()->create(['decimal_places' => 2]);
    $this->purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 1000,
        'min_quantity' => 1,
        'currency_id' => $this->currency->id,
        'priceable_type' => $this->purchasable->getMorphClass(),
        'priceable_id' => $this->purchasable->id,
    ]);
});

function redeemableDiscount(array $attributes = []): Discount
{
    $test = test();

    $discount = Discount::factory()->coupon('SAVE10')->create([
        'type' => FixedAmountOff::class,
        'name' => 'Ten off',
        'uses' => 0,
        'max_uses' => 1,
        'data' => ['amounts' => [$test->currency->code => 500]],
        ...$attributes,
    ]);

    $discount->customerGroups()->sync([
        $test->customerGroup->id => ['enabled' => true, 'starts_at' => now()->subHour()],
    ]);

    $discount->channels()->sync([
        $test->channel->id => ['enabled' => true, 'starts_at' => now()->subHour()],
    ]);

    return $discount;
}

function couponCart(array $attributes = []): Cart
{
    $test = test();

    // Each checkout is its own request, so nothing is memoised from the last.
    Discounts::resetDiscounts();

    $cart = Cart::factory()->create([
        'currency_id' => $test->currency->id,
        'channel_id' => $test->channel->id,
        'coupon_code' => 'SAVE10',
        ...$attributes,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $test->purchasable->getMorphClass(),
        'purchasable_id' => $test->purchasable->id,
        'quantity' => 2,
    ]);

    return $cart->calculate();
}

function withBillingEmail(Cart $cart, string $email): Cart
{
    CartAddress::factory()->create([
        'cart_id' => $cart->id,
        'type' => 'billing',
        'contact_email' => $email,
    ]);

    return $cart->refresh();
}

function draftOrderFor(Cart $cart): Order
{
    return app(CreateOrder::class)->execute($cart)->refresh();
}

test('an abandoned draft order uses nothing', function () {
    $discount = redeemableDiscount();

    $order = draftOrderFor(couponCart());

    expect($order->discount_total)->toEqual(500)
        ->and($discount->refresh()->uses)->toBe(0)
        ->and($discount->redemptions()->count())->toBe(0);
});

test('a declined card leaves the use for a new cart', function () {
    $discount = redeemableDiscount();

    // The card is declined, and the shopper comes back on another device.
    draftOrderFor(couponCart());

    $order = draftOrderFor(couponCart());

    expect($order->discount_total)->toEqual(500);

    $order->update(['placed_at' => now()]);

    expect($discount->refresh()->uses)->toBe(1)
        ->and($discount->redemptions()->sole()->order_id)->toBe($order->id);
});

test('placing an order records who redeemed each of its discounts', function () {
    setAuthUserConfig();

    $user = User::factory()->create();
    $discount = redeemableDiscount();
    $cart = couponCart(['user_id' => $user->id]);
    $order = draftOrderFor($cart);

    $order->update(['placed_at' => now()]);

    $redemption = $discount->redemptions()->sole();

    expect($redemption->order_id)->toBe($order->id)
        ->and($redemption->cart_id)->toBe($cart->id)
        ->and($redemption->user_id)->toEqual($user->id)
        ->and($redemption->email)->toBe(strtolower($user->email))
        ->and($redemption->redeemed_at->equalTo($order->placed_at))->toBeTrue()
        ->and($discount->users->pluck('id')->all())->toEqual([$user->id]);
});

test('an order placed again counts its discounts once', function () {
    $discount = redeemableDiscount(['max_uses' => 5]);
    $order = draftOrderFor(couponCart());

    $order->update(['placed_at' => now()]);
    OrderPlaced::dispatch($order);

    expect($discount->refresh()->uses)->toBe(1)
        ->and($discount->redemptions()->count())->toBe(1);
});

test('a placed order spends the last use for every other cart', function () {
    $discount = redeemableDiscount();

    draftOrderFor(couponCart())->update(['placed_at' => now()]);

    expect(draftOrderFor(couponCart())->discount_total)->toEqual(0)
        ->and($discount->refresh()->uses)->toBe(1);
});

test('a placement over the limit keeps the order and reports it', function () {
    Event::fake([DiscountLimitExceeded::class]);
    activity()->enableLogging();

    $discount = redeemableDiscount();

    // Two carts priced with the last use, and both paid before either placed.
    $first = draftOrderFor(couponCart());
    $second = draftOrderFor(couponCart());

    $first->update(['placed_at' => now()]);
    $second->update(['placed_at' => now()]);

    expect($discount->refresh()->uses)->toBe(2)
        ->and($second->refresh()->isPlaced())->toBeTrue()
        ->and($discount->redemptions()->count())->toBe(2);

    Event::assertDispatchedTimes(DiscountLimitExceeded::class, 1);
    Event::assertDispatched(
        DiscountLimitExceeded::class,
        fn (DiscountLimitExceeded $event) => $event->order->is($second)
            && $event->discount->is($discount)
            && $event->limit === DiscountLimit::MaxUses
            && $event->coupon?->code === 'SAVE10',
    );

    $activity = Activity::query()->where('event', 'discount-limit-exceeded')->sole();

    expect($activity->subject_id)->toBe($second->id)
        ->and($activity->properties->all())->toEqual([
            'discount_id' => $discount->id,
            'discount' => 'Ten off',
            'coupon' => 'SAVE10',
            'limit' => 'max_uses',
            'uses' => 2,
            'max_uses' => 1,
        ]);
});

test('a signed-in customer at their per-user limit no longer gets the discount', function () {
    setAuthUserConfig();

    $user = User::factory()->create();
    $discount = redeemableDiscount(['max_uses' => null, 'max_uses_per_user' => 1]);

    draftOrderFor(couponCart(['user_id' => $user->id]))->update(['placed_at' => now()]);

    expect(draftOrderFor(couponCart(['user_id' => $user->id]))->discount_total)->toEqual(0)
        ->and(draftOrderFor(couponCart(['user_id' => User::factory()->create()->id]))->discount_total)->toEqual(500)
        ->and($discount->refresh()->uses)->toBe(1);
});

test('placing an order records the customer\'s email, normalised', function () {
    $discount = redeemableDiscount();

    draftOrderFor(withBillingEmail(couponCart(), ' Guest@Example.com '))->update(['placed_at' => now()]);

    expect($discount->redemptions()->sole()->email)->toBe('guest@example.com');
});

test('a placement over the per-customer limit keeps the order and reports it', function () {
    Event::fake([DiscountLimitExceeded::class]);
    activity()->enableLogging();

    $discount = redeemableDiscount(['max_uses' => null, 'max_uses_per_user' => 1]);

    // The same guest pays for two carts before either is placed.
    $first = draftOrderFor(withBillingEmail(couponCart(), 'guest@example.com'));
    $second = draftOrderFor(withBillingEmail(couponCart(), 'guest@example.com'));

    expect($second->discount_total)->toEqual(500);

    $first->update(['placed_at' => now()]);
    $second->update(['placed_at' => now()]);

    expect($second->refresh()->isPlaced())->toBeTrue()
        ->and($discount->redemptions()->where('email', 'guest@example.com')->count())->toBe(2);

    Event::assertDispatchedTimes(DiscountLimitExceeded::class, 1);
    Event::assertDispatched(
        DiscountLimitExceeded::class,
        fn (DiscountLimitExceeded $event) => $event->order->is($second)
            && $event->limit === DiscountLimit::MaxUsesPerUser,
    );

    expect(Activity::query()->where('event', 'discount-limit-exceeded')->sole()->properties->all())
        ->toMatchArray([
            'limit' => 'max_uses_per_user',
            'uses' => 2,
            'max_uses' => 1,
        ]);
});

test('a guest at their per-customer limit no longer gets the discount', function () {
    $discount = redeemableDiscount(['max_uses' => null, 'max_uses_per_user' => 1]);

    draftOrderFor(withBillingEmail(couponCart(), 'guest@example.com'))->update(['placed_at' => now()]);

    expect(draftOrderFor(withBillingEmail(couponCart(), 'GUEST@example.com'))->discount_total)->toEqual(0)
        ->and(draftOrderFor(withBillingEmail(couponCart(), 'other@example.com'))->discount_total)->toEqual(500)
        ->and($discount->refresh()->uses)->toBe(1);
});

test('placing an order records the coupon and counts its use', function () {
    $discount = redeemableDiscount(['max_uses' => null]);
    $coupon = $discount->coupons()->sole();

    draftOrderFor(couponCart())->update(['placed_at' => now()]);

    expect($coupon->refresh()->uses)->toBe(1)
        ->and($discount->refresh()->uses)->toBe(1)
        ->and($discount->redemptions()->sole()->coupon_id)->toBe($coupon->id);
});

test('a guest redeems a single-use code once, and no other cart can', function () {
    $discount = redeemableDiscount(['max_uses' => null]);
    $discount->coupons()->sole()->update(['max_uses' => 1]);

    $order = draftOrderFor(couponCart());

    expect($order->discount_total)->toEqual(500);

    $order->update(['placed_at' => now()]);

    expect(draftOrderFor(couponCart())->discount_total)->toEqual(0);
});

test('a discount with several codes applies to each and counts each', function () {
    $discount = redeemableDiscount(['max_uses' => null]);
    Coupon::factory()->for($discount)->create(['code' => 'SAVE10-B']);

    draftOrderFor(couponCart())->update(['placed_at' => now()]);
    draftOrderFor(couponCart(['coupon_code' => 'save10-b']))->update(['placed_at' => now()]);

    expect($discount->refresh()->uses)->toBe(2)
        ->and($discount->coupons()->pluck('uses', 'code')->all())->toEqual(['SAVE10' => 1, 'SAVE10-B' => 1]);
});

test('a code without a use left no longer applies, though its discount has uses left', function () {
    $discount = redeemableDiscount(['max_uses' => null]);
    $discount->coupons()->sole()->update(['uses' => 3, 'max_uses' => 3]);

    expect(draftOrderFor(couponCart())->discount_total)->toEqual(0);
});

test('a coupon over its own limit at placement keeps the order and reports it', function () {
    Event::fake([DiscountLimitExceeded::class]);
    activity()->enableLogging();

    $discount = redeemableDiscount(['max_uses' => null]);
    $coupon = $discount->coupons()->sole();
    $coupon->update(['max_uses' => 1]);

    $first = draftOrderFor(couponCart());
    $second = draftOrderFor(couponCart());

    $first->update(['placed_at' => now()]);
    $second->update(['placed_at' => now()]);

    expect($coupon->refresh()->uses)->toBe(2);

    Event::assertDispatchedTimes(DiscountLimitExceeded::class, 1);
    Event::assertDispatched(
        DiscountLimitExceeded::class,
        fn (DiscountLimitExceeded $event) => $event->order->is($second)
            && $event->limit === DiscountLimit::CouponMaxUses
            && $event->coupon->is($coupon),
    );

    expect(Activity::query()->where('event', 'discount-limit-exceeded')->sole()->properties->all())->toEqual([
        'discount_id' => $discount->id,
        'discount' => 'Ten off',
        'coupon' => 'SAVE10',
        'limit' => 'coupon_max_uses',
        'uses' => 2,
        'max_uses' => 1,
    ]);
});

test('a coupon deleted before placement records the redemption without it', function () {
    $discount = redeemableDiscount(['max_uses' => null]);

    $order = draftOrderFor(couponCart());

    $discount->coupons()->delete();

    $order->update(['placed_at' => now()]);

    expect($discount->refresh()->uses)->toBe(1)
        ->and($discount->redemptions()->sole()->coupon_id)->toBeNull();
});
