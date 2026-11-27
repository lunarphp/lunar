<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Contracts\Actions\Discounts\ReconcilesRewardLines;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\DiscountTypes\BuyXGetY;
use Lunar\Core\Enums\StepList;
use Lunar\Core\Exceptions\Carts\CartException;
use Lunar\Core\Facades\Discounts;
use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Facades\Steps;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartAddress;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\TaxClass;
use Lunar\Core\Modifiers\ShippingModifier;
use Lunar\Core\Modifiers\ShippingModifiers;
use Lunar\Tests\Core\Stubs\CartPipelineSpy;
use Lunar\Tests\Core\TestCase;
use Spatie\LaravelBlink\BlinkFacade as Blink;

uses(TestCase::class)->group('carts', 'discounts', 'cross-db');

uses(RefreshDatabase::class);

beforeEach(function () {
    CartPipelineSpy::reset();

    Steps::for(StepList::CartCalculation)->prepend(CartPipelineSpy::class);
});

/**
 * "Buy A, get B free": both £10, B added automatically. The condition is A
 * itself, or a collection holding A and B when $collectionCondition is set.
 *
 * @return array{cart: Cart, a: ProductVariant, b: ProductVariant, discount: Discount, channel: Channel}
 */
function rewardLinesSetUp(array $data = [], bool $collectionCondition = false): array
{
    $customerGroup = CustomerGroup::factory()->create(['default' => true]);
    $channel = Channel::factory()->create(['default' => true]);
    $currency = Currency::factory()->create(['code' => 'GBP', 'decimal_places' => 2, 'default' => true]);

    $cart = Cart::factory()->create(['channel_id' => $channel->id, 'currency_id' => $currency->id]);

    $productA = Product::factory()->create();
    $productB = Product::factory()->create();

    $a = ProductVariant::factory()->create(['product_id' => $productA->id]);
    $b = ProductVariant::factory()->create(['product_id' => $productB->id]);

    foreach ([$a, $b] as $variant) {
        Price::factory()->create([
            'price' => 1000,
            'min_quantity' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => $variant->getMorphClass(),
            'priceable_id' => $variant->id,
        ]);
    }

    $discount = Discount::factory()->create([
        'type' => BuyXGetY::class,
        'name' => 'Free B with A',
        'data' => $data + ['min_qty' => 1, 'reward_qty' => 1, 'automatically_add_rewards' => true],
    ]);

    $discount->customerGroups()->sync([$customerGroup->id => ['enabled' => true, 'starts_at' => now()]]);
    $discount->channels()->sync([$channel->id => ['enabled' => true, 'starts_at' => now()->subHour()]]);

    if ($collectionCondition) {
        $collection = Collection::factory()->create();
        $productA->collections()->sync($collection);
        $productB->collections()->sync($collection);

        $discount->discountableConditions()->create(['discountable_type' => $collection->getMorphClass(), 'discountable_id' => $collection->id]);
    } else {
        $discount->discountableConditions()->create(['discountable_type' => $productA->getMorphClass(), 'discountable_id' => $productA->id]);
    }

    $discount->discountableRewards()->create(['discountable_type' => $productB->getMorphClass(), 'discountable_id' => $productB->id, 'type' => 'reward']);

    return ['cart' => $cart, 'a' => $a, 'b' => $b, 'discount' => $discount, 'channel' => $channel];
}

function rewardLinesAdd(Cart $cart, ProductVariant $variant, int $quantity): CartLine
{
    return $cart->lines()->create([
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
        'quantity' => $quantity,
    ]);
}

/**
 * Calculate the cart as the next request would see it.
 */
function rewardLinesCalculate(Cart $cart): Cart
{
    Blink::flush();
    Discounts::resetDiscounts();

    return Cart::query()->find($cart->id)->calculate();
}

function rewardLinesAdded(Cart $cart): Illuminate\Support\Collection
{
    return $cart->lines->filter(fn (CartLine $line) => $line->isAddedByDiscount())->values();
}

test('removes the reward line once the cart stops earning it', function () {
    ['cart' => $cart, 'a' => $a] = rewardLinesSetUp();

    $line = rewardLinesAdd($cart, $a, 1);

    expect(rewardLinesAdded(rewardLinesCalculate($cart)))->toHaveCount(1);

    $line->delete();

    $cart = rewardLinesCalculate($cart);

    expect($cart->lines)->toBeEmpty()
        ->and(CartLine::query()->where('cart_id', $cart->id)->count())->toBe(0)
        ->and($cart->total->value)->toBe(0);
});

test('removes the reward line when the discount stops applying', function () {
    ['cart' => $cart, 'a' => $a, 'discount' => $discount, 'channel' => $channel] = rewardLinesSetUp();

    rewardLinesAdd($cart, $a, 1);

    expect(rewardLinesAdded(rewardLinesCalculate($cart)))->toHaveCount(1);

    $discount->channels()->sync([$channel->id => [
        'enabled' => true,
        'starts_at' => now()->subHours(2),
        'ends_at' => now()->subHour(),
    ]]);

    // Ending a discount doesn't touch the cart, so the snapshot stays fresh
    // until its TTL; checkout recalculates.
    $cart = rewardLinesCalculate($cart)->recalculate();

    expect($cart->lines)->toHaveCount(1)
        ->and($cart->lines->first()->purchasable_id)->toBe($a->id)
        ->and($cart->total->value)->toBe($cart->lines->first()->total->value);
});

test('reduces the reward line to the units the cart still earns', function () {
    ['cart' => $cart, 'a' => $a, 'b' => $b] = rewardLinesSetUp();

    $line = rewardLinesAdd($cart, $a, 2);

    expect(rewardLinesAdded(rewardLinesCalculate($cart))->first()->quantity)->toBe(2);

    $line->update(['quantity' => 1]);

    $cart = rewardLinesCalculate($cart);
    $reward = rewardLinesAdded($cart)->sole();

    expect($reward->purchasable_id)->toBe($b->id)
        ->and($reward->quantity)->toBe(1)
        ->and($reward->fresh()->quantity)->toBe(1)
        ->and($reward->discountTotal->value)->toBe(1000)
        ->and($reward->total->value)->toBe(0);
});

test('raises the reward line to the units the cart earns', function () {
    ['cart' => $cart, 'a' => $a] = rewardLinesSetUp();

    $line = rewardLinesAdd($cart, $a, 1);

    rewardLinesCalculate($cart);

    $line->update(['quantity' => 3]);

    $cart = rewardLinesCalculate($cart);
    $reward = rewardLinesAdded($cart)->sole();

    expect($reward->quantity)->toBe(3)
        ->and($reward->discountTotal->value)->toBe(3000)
        ->and($reward->total->value)->toBe(0)
        ->and($cart->discountTotal->value)->toBe(3000);
});

test('a reward line does not count toward its own collection condition', function () {
    ['cart' => $cart, 'a' => $a] = rewardLinesSetUp(['min_qty' => 2], collectionCondition: true);

    $line = rewardLinesAdd($cart, $a, 2);

    expect(rewardLinesAdded(rewardLinesCalculate($cart)))->toHaveCount(1);

    // One A no longer meets min_qty 2; the added B must not make up the second.
    $line->update(['quantity' => 1]);

    $cart = rewardLinesCalculate($cart);

    expect(rewardLinesAdded($cart))->toBeEmpty()
        ->and($cart->discountTotal->value)->toBe(0);
});

test('a reward line does not count toward the minimum spend', function () {
    ['cart' => $cart, 'a' => $a] = rewardLinesSetUp(['min_prices' => ['GBP' => 1500]]);

    $line = rewardLinesAdd($cart, $a, 2);

    expect(rewardLinesAdded(rewardLinesCalculate($cart)))->toHaveCount(1);

    // £10 of A is under the £15 minimum; the free B's £10 must not make it up.
    $line->update(['quantity' => 1]);

    $cart = rewardLinesCalculate($cart);

    expect(rewardLinesAdded($cart))->toBeEmpty()
        ->and($cart->discountTotal->value)->toBe(0);
});

test('the reward moves onto the reward product the shopper adds', function () {
    ['cart' => $cart, 'a' => $a, 'b' => $b] = rewardLinesSetUp();

    rewardLinesAdd($cart, $a, 1);

    $before = rewardLinesCalculate($cart)->total->value;

    $shopperLine = rewardLinesAdd($cart, $b, 1);

    $cart = rewardLinesCalculate($cart);
    $line = $cart->lines->firstWhere('id', $shopperLine->id);

    expect(rewardLinesAdded($cart))->toBeEmpty()
        ->and($line->isAddedByDiscount())->toBeFalse()
        ->and($line->discountTotal->value)->toBe(1000)
        ->and($cart->total->value)->toBe($before);
});

test('keeps the older of two reward lines from one discount', function () {
    ['cart' => $cart, 'a' => $a, 'b' => $b, 'discount' => $discount] = rewardLinesSetUp();

    rewardLinesAdd($cart, $a, 1);

    $older = rewardLinesAdded(rewardLinesCalculate($cart))->sole();

    // A second added line for the same reward, as a merged guest cart brings.
    $newer = $cart->lines()->create([
        'purchasable_type' => $b->getMorphClass(),
        'purchasable_id' => $b->id,
        'quantity' => 1,
        'added_by_discount_id' => $discount->id,
    ]);

    $cart = rewardLinesCalculate($cart);

    expect(rewardLinesAdded($cart)->pluck('id')->all())->toBe([$older->id])
        ->and(CartLine::query()->find($newer->id))->toBeNull()
        ->and($cart->discountTotal->value)->toBe(1000);
});

test('removes the reward lines when automatic rewards are turned off', function () {
    ['cart' => $cart, 'a' => $a, 'discount' => $discount] = rewardLinesSetUp();

    rewardLinesAdd($cart, $a, 1);

    expect(rewardLinesAdded(rewardLinesCalculate($cart)))->toHaveCount(1);

    $discount->update(['data' => ['min_qty' => 1, 'reward_qty' => 1, 'automatically_add_rewards' => false]]);

    expect(rewardLinesAdded(rewardLinesCalculate($cart)->recalculate()))->toBeEmpty();
});

test('the second pass leaves nothing to reconcile', function () {
    ['cart' => $cart, 'a' => $a] = rewardLinesSetUp();

    $line = rewardLinesAdd($cart, $a, 1);

    $cart = rewardLinesCalculate($cart);

    expect(CartPipelineSpy::$runs)->toBe(2)
        ->and(app(ReconcilesRewardLines::class)->execute($cart))->toBeFalse();

    // A calculation that changes no added line runs the steps once.
    CartPipelineSpy::reset();
    $line->update(['meta' => ['gift_note' => 'hello']]);

    $cart = rewardLinesCalculate($cart);

    expect(CartPipelineSpy::$runs)->toBe(1)
        ->and(rewardLinesAdded($cart))->toHaveCount(1);
});

test('shipping sees the reward line in the calculation that adds it', function () {
    ['cart' => $cart, 'a' => $a] = rewardLinesSetUp();

    CartAddress::factory()->create([
        'cart_id' => $cart->id,
        'type' => 'shipping',
        'country_id' => Country::factory()->create()->id,
        'shipping_option' => 'PERUNIT',
    ]);

    app(ShippingModifiers::class)->add(PerUnitShippingModifier::class);

    rewardLinesAdd($cart, $a, 1);

    $cart = rewardLinesCalculate($cart);

    // £1 a unit: A and the added B.
    expect(rewardLinesAdded($cart))->toHaveCount(1)
        ->and($cart->shippingSubTotal->value)->toBe(200);
});

test('recalculating the same cart does not repeat its free items', function () {
    ['cart' => $cart, 'a' => $a] = rewardLinesSetUp();

    rewardLinesAdd($cart, $a, 1);

    $cart = rewardLinesCalculate($cart);
    $cart->recalculate();
    $cart->recalculate();

    expect($cart->freeItems)->toHaveCount(1);
});

test('refuses a quantity change on a reward line', function () {
    ['cart' => $cart, 'a' => $a] = rewardLinesSetUp();

    $shopperLine = rewardLinesAdd($cart, $a, 1);

    $cart = rewardLinesCalculate($cart);
    $reward = rewardLinesAdded($cart)->sole();

    expect(fn () => $cart->updateLine($reward->id, 3))
        ->toThrow(CartException::class, __('lunar::exceptions.carts.line_added_by_discount'));

    expect($reward->fresh()->quantity)->toBe(1);

    // The shopper's own line still changes, and the reward follows it.
    $cart->updateLine($shopperLine->id, 2);

    expect(rewardLinesAdded(rewardLinesCalculate($cart))->sole()->quantity)->toBe(2);
});

test('accepts a meta change on a reward line at the same quantity', function () {
    ['cart' => $cart, 'a' => $a] = rewardLinesSetUp();

    rewardLinesAdd($cart, $a, 1);

    $cart = rewardLinesCalculate($cart);
    $reward = rewardLinesAdded($cart)->sole();

    $cart->updateLine($reward->id, 1, ['gift_note' => 'Happy birthday']);

    expect($reward->fresh()->meta['gift_note'])->toBe('Happy birthday')
        ->and($reward->fresh()->quantity)->toBe(1);
});

/**
 * Offers PERUNIT at £1 for every unit in the cart, so its price shows which
 * lines shipping was resolved against.
 */
class PerUnitShippingModifier extends ShippingModifier
{
    public function handle(Cart $cart, Closure $next)
    {
        ShippingManifest::addOption(new ShippingOption(
            name: 'Per unit',
            description: 'Per unit',
            identifier: 'PERUNIT',
            price: new PriceValue(100 * (int) $cart->lines->sum('quantity'), $cart->currency),
            taxClass: TaxClass::query()->first() ?? TaxClass::factory()->create(),
        ));

        return $next($cart);
    }
}
