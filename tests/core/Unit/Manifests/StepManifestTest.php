<?php

namespace Lunar\Tests\Core\Unit\Manifests;

use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Contracts\StepManifest;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Enums\StepList;
use Lunar\Core\Exceptions\RemovedStepConfigException;
use Lunar\Core\Exceptions\UnknownStepException;
use Lunar\Core\Facades\Steps;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Pipelines\Cart\ApplyDiscounts;
use Lunar\Core\Pipelines\Cart\ApplyShipping;
use Lunar\Core\Pipelines\Cart\Calculate;
use Lunar\Core\Pipelines\Cart\CalculateLines;
use Lunar\Core\Pipelines\Cart\CalculateShippingSubTotal;
use Lunar\Core\Pipelines\Cart\CalculateTax;
use Lunar\Core\Pipelines\CartLine\GetUnitPrice;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

// Stand-in step classes; the manifest only orders class names.
class StepA {}
class StepB {}
class StepC {}

class DoubleUnitPrice
{
    public function handle(CartLine $cartLine, Closure $next): mixed
    {
        $cartLine->unitPrice = new PriceValue($cartLine->unitPrice->value * 2, $cartLine->cart->currency);

        return $next($cartLine);
    }
}

test('lists are seeded with the core defaults', function () {
    expect(Steps::for(StepList::CartCalculation)->all())->toBe([
        CalculateLines::class,
        ApplyShipping::class,
        CalculateShippingSubTotal::class,
        ApplyDiscounts::class,
        CalculateTax::class,
        Calculate::class,
    ])->and(Steps::for(StepList::CartLinePricing)->all())->toBe([GetUnitPrice::class])
        ->and(Steps::for(StepList::PriceMatching)->all())->toBe([]);
});

test('the manifest is a singleton', function () {
    Steps::for(StepList::PriceMatching)->append(StepA::class);

    expect(app(StepManifest::class))->toBe(app(StepManifest::class))
        ->and(app(StepManifest::class)->for(StepList::PriceMatching)->all())->toBe([StepA::class]);
});

test('steps can be placed at each position', function () {
    Steps::for(StepList::CartCalculation)
        ->prepend(StepA::class)
        ->after(ApplyShipping::class, StepB::class)
        ->before(Calculate::class, StepC::class)
        ->remove(CalculateShippingSubTotal::class)
        ->replace(ApplyDiscounts::class, GetUnitPrice::class);

    expect(Steps::for(StepList::CartCalculation)->all())->toBe([
        StepA::class,
        CalculateLines::class,
        ApplyShipping::class,
        StepB::class,
        GetUnitPrice::class,
        CalculateTax::class,
        StepC::class,
        Calculate::class,
    ]);
});

test('adding a step that is already in the list does nothing', function () {
    Steps::for(StepList::CartLinePricing)
        ->append(StepA::class)
        ->append(StepA::class)
        ->after(GetUnitPrice::class, StepA::class)
        ->prepend(GetUnitPrice::class);

    expect(Steps::for(StepList::CartLinePricing)->all())->toBe([GetUnitPrice::class, StepA::class]);
});

test('steps placed after the same anchor keep their registration order', function () {
    Steps::for(StepList::CartLinePricing)
        ->append(StepC::class)
        ->after(GetUnitPrice::class, StepA::class)
        ->after(GetUnitPrice::class, StepB::class);

    expect(Steps::for(StepList::CartLinePricing)->all())
        ->toBe([GetUnitPrice::class, StepA::class, StepB::class, StepC::class]);
});

test('an anchor that is not in the list throws when the list is read', function () {
    Steps::for(StepList::CartLinePricing)
        ->remove(GetUnitPrice::class)
        ->after(GetUnitPrice::class, StepA::class);

    expect(fn () => Steps::for(StepList::CartLinePricing)->all())
        ->toThrow(UnknownStepException::class, 'Cannot position ['.StepA::class.'] against ['.GetUnitPrice::class.'] in the CartLinePricing step list');
});

test('replacing a step that is not in the list throws', function () {
    expect(fn () => Steps::for(StepList::CartLinePricing)->replace(StepA::class, StepB::class)->all())
        ->toThrow(UnknownStepException::class);
});

test('a step registered after GetUnitPrice runs during cart calculation', function () {
    $currency = Currency::factory()->create();
    $cart = Cart::factory()->create(['currency_id' => $currency->id]);
    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 100,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    Steps::for(StepList::CartLinePricing)->after(GetUnitPrice::class, DoubleUnitPrice::class);

    $cart->calculate();

    expect($cart->lines->first()->unitPrice->value)->toBe(200)
        ->and($cart->subTotal->value)->toBe(200);
});

test('a removed step-list config key fails when a list is first read', function (string $key, string $expected) {
    config()->set($key, []);

    expect(fn () => Steps::for(StepList::PriceMatching))
        ->toThrow(RemovedStepConfigException::class, $expected);
})->with([
    'cart pipelines' => ['lunar.cart.pipelines', 'Steps::for(StepList::CartCalculation), Steps::for(StepList::CartLinePricing)'],
    'cart validators' => ['lunar.cart.validators', 'Steps::for(StepList::AddToCartValidation)'],
    'prune pipelines' => ['lunar.cart.prune_tables.pipelines', 'Steps::for(StepList::CartPruning)'],
    'pricing pipelines' => ['lunar.pricing.pipelines', 'Steps::for(StepList::PriceMatching)'],
    'order pipelines' => ['lunar.orders.pipelines', 'Steps::for(StepList::OrderCreation)'],
]);

test('lunar:steps lists each resolved list', function () {
    Steps::for(StepList::CartLinePricing)->after(GetUnitPrice::class, StepA::class);

    $this->artisan('lunar:steps')
        ->expectsOutputToContain('CartLinePricing')
        ->expectsOutputToContain('1. '.GetUnitPrice::class)
        ->expectsOutputToContain('2. '.StepA::class)
        ->assertSuccessful();
});
