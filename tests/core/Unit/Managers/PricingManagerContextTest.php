<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Contracts\Actions\Storefront\ResolvesStorefrontContext;
use Lunar\Core\Contracts\DefaultStorefrontContext;
use Lunar\Core\Enums\StepList;
use Lunar\Core\Exceptions\MissingCurrencyPriceException;
use Lunar\Core\Exceptions\MissingStorefrontContextException;
use Lunar\Core\Facades\Pricing;
use Lunar\Core\Facades\Steps;
use Lunar\Core\Facades\StorefrontSession;
use Lunar\Core\Managers\PricingManager;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Region;
use Lunar\Core\Models\TaxClass;
use Lunar\Tests\Core\Stubs\CapturePricingContextPipeline;
use Lunar\Tests\Core\Stubs\User;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->channel = Channel::factory()->create(['default' => true]);
    $this->currency = Currency::factory()->create(['default' => true, 'code' => 'USD', 'exchange_rate' => 1]);
    $this->defaultGroup = CustomerGroup::factory()->create(['default' => true]);
    Language::factory()->create(['default' => true]);

    $this->variant = ProductVariant::factory()->create([
        'product_id' => Product::factory()->create(['status' => 'published'])->id,
    ]);

    $this->price = fn (int $price, array $attributes = []) => Price::factory()->create([
        'price' => $price,
        'priceable_type' => $this->variant->getMorphClass(),
        'priceable_id' => $this->variant->id,
        'currency_id' => $this->currency->id,
        'min_quantity' => 1,
        ...$attributes,
    ]);

    CapturePricingContextPipeline::$context = null;
    CapturePricingContextPipeline::$legacyCurrencyCode = null;
});

afterEach(fn () => PricingManager::preventDefaultContext(false));

function customerInGroup(CustomerGroup $group): Customer
{
    $customer = Customer::factory()->create();
    $customer->customerGroups()->attach($group);

    return $customer;
}

test('no state survives a successful get', function () {
    $gbp = Currency::factory()->create(['default' => false, 'code' => 'GBP']);
    $base = ($this->price)(100);
    ($this->price)(90, ['currency_id' => $gbp->id]);

    expect(Pricing::for($this->variant)->currency($gbp)->qty(5)->get()->matched->price)->toEqual(90);

    $next = Pricing::for($this->variant)->get();

    expect($next->matched->id)->toEqual($base->id)
        ->and(Pricing::getFacadeRoot()->qty)->toEqual(1);
});

test('no state survives a get that throws', function () {
    $gbp = Currency::factory()->create(['default' => false, 'code' => 'GBP']);
    $base = ($this->price)(100);
    ($this->price)(50, ['min_quantity' => 10]);

    expect(fn () => Pricing::for($this->variant)->currency($gbp)->qty(10)->get())
        ->toThrow(MissingCurrencyPriceException::class);

    expect(Pricing::for($this->variant)->get()->matched->id)->toEqual($base->id);
});

test('a bare call ignores the authenticated user and the session', function () {
    $group = CustomerGroup::factory()->create(['default' => false]);
    $base = ($this->price)(100);
    ($this->price)(60, ['customer_group_id' => $group->id]);

    $user = User::factory()->create();
    $user->customers()->sync(customerInGroup($group)->id);
    $this->actingAs($user);

    expect(StorefrontSession::getCustomer())->not->toBeNull()
        ->and(Pricing::for($this->variant)->get()->matched->id)->toEqual($base->id);
});

test('explicit customer groups are not overridden by the authenticated user', function () {
    $explicit = CustomerGroup::factory()->create(['default' => false]);
    $usersGroup = CustomerGroup::factory()->create(['default' => false]);
    ($this->price)(100);
    $explicitPrice = ($this->price)(80, ['customer_group_id' => $explicit->id]);
    ($this->price)(60, ['customer_group_id' => $usersGroup->id]);

    $user = User::factory()->create();
    $user->customers()->sync(customerInGroup($usersGroup)->id);
    $this->actingAs($user);

    $pricing = Pricing::for($this->variant)->customerGroups(collect([$explicit]))->get();

    expect($pricing->matched->id)->toEqual($explicitPrice->id);
});

test('customer() derives the customer groups and names the customer', function () {
    $group = CustomerGroup::factory()->create(['default' => false]);
    ($this->price)(100);
    $groupPrice = ($this->price)(70, ['customer_group_id' => $group->id]);
    $customer = customerInGroup($group);

    Steps::for(StepList::PriceMatching)->append(CapturePricingContextPipeline::class);

    $pricing = Pricing::for($this->variant)->customer($customer)->get();

    expect($pricing->matched->id)->toEqual($groupPrice->id)
        ->and(CapturePricingContextPipeline::$context->customer->id)->toEqual($customer->id);
});

test('the deprecated user() names the customer only when the user has exactly one', function () {
    $groupA = CustomerGroup::factory()->create(['default' => false]);
    $groupB = CustomerGroup::factory()->create(['default' => false]);
    ($this->price)(100);
    Steps::for(StepList::PriceMatching)->append(CapturePricingContextPipeline::class);

    $single = User::factory()->create();
    $customer = customerInGroup($groupA);
    $single->customers()->sync($customer->id);

    Pricing::for($this->variant)->user($single)->get();

    expect(CapturePricingContextPipeline::$context->customer->id)->toEqual($customer->id);

    $several = User::factory()->create();
    $several->customers()->sync([customerInGroup($groupA)->id, customerInGroup($groupB)->id]);

    Pricing::for($this->variant)->user($several)->get();

    expect(CapturePricingContextPipeline::$context->customer)->toBeNull()
        ->and(CapturePricingContextPipeline::$context->customerGroups->pluck('id')->sort()->values()->all())
        ->toEqual(collect([$groupA->id, $groupB->id])->sort()->values()->all());
});

test('a registered default resolver supplies the context for a bare call', function () {
    $group = CustomerGroup::factory()->create(['default' => false]);
    ($this->price)(100);
    $groupPrice = ($this->price)(70, ['customer_group_id' => $group->id]);

    app(DefaultStorefrontContext::class)->resolveUsing(
        fn () => app(ResolvesStorefrontContext::class)->execute(customerGroups: collect([$group]))
    );

    expect(Pricing::for($this->variant)->get()->matched->id)->toEqual($groupPrice->id);
});

test('a supplied context beats a registered default resolver', function () {
    $group = CustomerGroup::factory()->create(['default' => false]);
    $base = ($this->price)(100);
    ($this->price)(70, ['customer_group_id' => $group->id]);

    app(DefaultStorefrontContext::class)->resolveUsing(
        fn () => app(ResolvesStorefrontContext::class)->execute(customerGroups: collect([$group]))
    );

    $pricing = $this->variant->pricing(app(ResolvesStorefrontContext::class)->execute())->get();

    expect($pricing->matched->id)->toEqual($base->id);
});

test('useAsDefaultContext prices bare calls for the session and sees later changes', function () {
    $group = CustomerGroup::factory()->create(['default' => false]);
    $base = ($this->price)(100);
    $groupPrice = ($this->price)(70, ['customer_group_id' => $group->id]);

    StorefrontSession::useAsDefaultContext();

    expect(Pricing::for($this->variant)->get()->matched->id)->toEqual($base->id);

    StorefrontSession::setCustomerGroup($group);

    expect(Pricing::for($this->variant)->get()->matched->id)->toEqual($groupPrice->id);
});

test('a registered default resolver does not survive the scope', function () {
    StorefrontSession::useAsDefaultContext();

    expect(app(DefaultStorefrontContext::class)->hasResolver())->toBeTrue();

    app()->forgetScopedInstances();

    expect(app(DefaultStorefrontContext::class)->hasResolver())->toBeFalse();
});

test('strict mode throws only when no context is supplied and no resolver is registered', function () {
    ($this->price)(100);
    PricingManager::preventDefaultContext();

    expect(fn () => Pricing::for($this->variant)->get())->toThrow(MissingStorefrontContextException::class)
        ->and(fn () => Pricing::for($this->variant)->currency($this->currency)->get())->toThrow(MissingStorefrontContextException::class);

    $context = app(ResolvesStorefrontContext::class)->execute();

    expect($this->variant->pricing($context)->get()->matched->price)->toEqual(100);

    StorefrontSession::useAsDefaultContext();

    expect(Pricing::for($this->variant)->get()->matched->price)->toEqual(100);
});

test('a pricing pipeline reads region and customer from the storefront path', function () {
    ($this->price)(100);
    Steps::for(StepList::PriceMatching)->append(CapturePricingContextPipeline::class);

    $region = Region::factory()->create([
        'default' => false,
        'channel_id' => $this->channel->id,
        'currency_id' => $this->currency->id,
    ]);
    $customer = customerInGroup($this->defaultGroup);

    $context = app(ResolvesStorefrontContext::class)->execute(customer: $customer, region: $region);

    $this->variant->pricing($context)->get();

    expect(CapturePricingContextPipeline::$context->region->id)->toEqual($region->id)
        ->and(CapturePricingContextPipeline::$context->customer->id)->toEqual($customer->id)
        ->and(CapturePricingContextPipeline::$legacyCurrencyCode)->toEqual('USD');
});

test('a pricing pipeline reads region and customer from the cart path', function () {
    ($this->price)(100);
    TaxClass::factory()->create(['default' => true]);
    Steps::for(StepList::PriceMatching)->append(CapturePricingContextPipeline::class);

    Region::factory()->create([
        'default' => true,
        'channel_id' => $this->channel->id,
        'currency_id' => $this->currency->id,
    ]);
    $cartRegion = Region::factory()->create([
        'default' => false,
        'channel_id' => $this->channel->id,
        'currency_id' => $this->currency->id,
    ]);
    $customer = customerInGroup($this->defaultGroup);

    $cart = Cart::factory()->create([
        'currency_id' => $this->currency->id,
        'channel_id' => $this->channel->id,
        'customer_id' => $customer->id,
        'region_id' => $cartRegion->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $this->variant->getMorphClass(),
        'purchasable_id' => $this->variant->id,
        'quantity' => 1,
    ]);

    expect($cart->context()->region->id)->toEqual($cartRegion->id);

    $cart->calculate();

    expect(CapturePricingContextPipeline::$context->region->id)->toEqual($cartRegion->id)
        ->and(CapturePricingContextPipeline::$context->customer->id)->toEqual($customer->id);
});

test('the legacy public properties are read-only', function () {
    $manager = app(PricingManager::class);

    expect(fn () => $manager->currency = $this->currency)->toThrow(Error::class);
});
