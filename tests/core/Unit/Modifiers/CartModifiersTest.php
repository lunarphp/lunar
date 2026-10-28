<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\TaxClass;
use Lunar\Core\Models\TaxRateAmount;
use Lunar\Core\Modifiers\CartLineModifier;
use Lunar\Core\Modifiers\CartLineModifiers;
use Lunar\Core\Modifiers\CartModifier;
use Lunar\Core\Modifiers\CartModifiers;
use Lunar\Tests\Core\Stubs\TestCartModifier;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

function cartWithTaxedLines(int $lineCount = 1): Cart
{
    $currency = Currency::factory()->create(['decimal_places' => 2]);

    $cart = Cart::factory()->create(['currency_id' => $currency->id]);

    $taxClass = TaxClass::factory()->create();

    $taxClass->taxRateAmounts()->create(
        TaxRateAmount::factory()->make([
            'percentage' => 20,
            'tax_class_id' => $taxClass->id,
        ])->toArray()
    );

    foreach (range(1, $lineCount) as $index) {
        $purchasable = ProductVariant::factory()->create(['tax_class_id' => $taxClass->id]);

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
    }

    return $cart;
}

function recordingLineModifier(ArrayObject $log, string $name = 'line'): CartLineModifier
{
    return new class($log, $name) extends CartLineModifier
    {
        public function __construct(protected ArrayObject $log, protected string $name) {}

        public function calculating(CartLine $cartLine, Closure $next): CartLine
        {
            $this->log[] = "{$this->name}:calculating:{$cartLine->id}";

            return $next($cartLine);
        }

        public function subtotalled(CartLine $cartLine, Closure $next): CartLine
        {
            $this->log[] = "{$this->name}:subtotalled:{$cartLine->id}";

            return $next($cartLine);
        }

        public function calculated(CartLine $cartLine, Closure $next): CartLine
        {
            $this->log[] = "{$this->name}:calculated:{$cartLine->id}";

            return $next($cartLine);
        }
    };
}

test('line and cart hooks fire once each, in order, during calculate', function () {
    $cart = cartWithTaxedLines(2);
    [$first, $second] = $cart->lines->pluck('id')->all();
    $log = new ArrayObject;

    app(CartLineModifiers::class)->add(recordingLineModifier($log));
    app(CartModifiers::class)->add(new class($log) extends CartModifier
    {
        public function __construct(protected ArrayObject $log) {}

        public function calculating(Cart $cart, Closure $next): Cart
        {
            $this->log[] = 'cart:calculating';

            return $next($cart);
        }

        public function calculated(Cart $cart, Closure $next): Cart
        {
            $this->log[] = 'cart:calculated';

            return $next($cart);
        }
    });

    $cart->calculate();

    expect($log->getArrayCopy())->toBe([
        'cart:calculating',
        "line:calculating:{$first}",
        "line:subtotalled:{$first}",
        "line:calculating:{$second}",
        "line:subtotalled:{$second}",
        "line:calculated:{$first}",
        "line:calculated:{$second}",
        'cart:calculated',
    ]);
});

test('line modifiers run in registration order', function () {
    $cart = cartWithTaxedLines();
    $lineId = $cart->lines->first()->id;
    $log = new ArrayObject;

    app(CartLineModifiers::class)->add(recordingLineModifier($log, 'first'));
    app(CartLineModifiers::class)->add(recordingLineModifier($log, 'second'));

    $cart->calculate();

    expect(array_slice($log->getArrayCopy(), 0, 4))->toBe([
        "first:calculating:{$lineId}",
        "second:calculating:{$lineId}",
        "first:subtotalled:{$lineId}",
        "second:subtotalled:{$lineId}",
    ]);
});

test('a subtotal changed in subtotalled reaches tax and the cart total', function () {
    $cart = cartWithTaxedLines();

    app(CartLineModifiers::class)->add(new class extends CartLineModifier
    {
        public function subtotalled(CartLine $cartLine, Closure $next): CartLine
        {
            $cartLine->subTotal = new PriceValue(50, $cartLine->cart->currency);

            return $next($cartLine);
        }
    });

    $cart->calculate();

    $line = $cart->lines->first();

    expect($line->unitPrice->value)->toBe(100)
        ->and($line->subTotal->value)->toBe(50)
        ->and($line->taxAmount->value)->toBe(10)
        ->and($line->total->value)->toBe(60)
        ->and($cart->subTotal->value)->toBe(50)
        ->and($cart->taxTotal->value)->toBe(10)
        ->and($cart->total->value)->toBe(60);
});

test('the line calculated hook sees final line totals', function () {
    $cart = cartWithTaxedLines();
    $seen = new ArrayObject;

    app(CartLineModifiers::class)->add(new class($seen) extends CartLineModifier
    {
        public function __construct(protected ArrayObject $seen) {}

        public function calculated(CartLine $cartLine, Closure $next): CartLine
        {
            $this->seen['taxAmount'] = $cartLine->taxAmount->value;
            $this->seen['total'] = $cartLine->total->value;

            return $next($cartLine);
        }
    });

    $cart->calculate();

    expect($seen->getArrayCopy())->toBe(['taxAmount' => 20, 'total' => 120]);
});

test('the cart calculated hook can change the cart totals', function () {
    $cart = cartWithTaxedLines();

    app(CartModifiers::class)->add(new TestCartModifier);

    $cart->calculate();

    expect($cart->total->value)->toBe(5000);
});
