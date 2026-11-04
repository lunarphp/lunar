<?php

use Illuminate\Support\Collection;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Enums\HoldAdjustment;
use Lunar\Core\Enums\PaymentIntentStatus;
use Lunar\Core\Exceptions\PaymentIntentException;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Currency;
use Lunar\Stripe\Facades\Stripe;
use Lunar\Stripe\Managers\StripeManager;
use Lunar\Stripe\MockClient;
use Lunar\Stripe\Models\StripePaymentIntent;
use Lunar\Stripe\StripePaymentType;
use Lunar\Tests\Stripe\Unit\TestCase;
use Lunar\Tests\Stripe\Utils\CartBuilder;
use Stripe\ApiRequestor;
use Stripe\PaymentIntent;

use function Pest\Laravel\assertDatabaseHas;

uses(TestCase::class);

/**
 * Resolve the Stripe payment driver the way the payment type tests do
 * (StripePaymentTypeTest.php: `new StripePaymentType`), since the hold
 * verbs live on the driver, not the manager.
 */
function paymentDriver(): StripePaymentType
{
    return new StripePaymentType;
}

it('can create a payment intent', function () {
    $cart = CartBuilder::build();

    $intent = Stripe::createIntent($cart->calculate(), []);

    assertDatabaseHas(StripePaymentIntent::class, [
        'intent_id' => 'pi_1DqH152eZvKYlo2CFHYZuxkP',
        'cart_id' => $cart->id,
        'status' => $intent->status,
    ]);
});

it('replaces a canceled intent instead of reusing it', function () {
    $cart = CartBuilder::build();

    // The local record still reads active, but Stripe reports the intent
    // canceled (dashboard cancellation, another surface finishing it).
    $cart->paymentIntents()->create([
        'intent_id' => 'PI_CANCELED',
        'status' => 'requires_payment_method',
    ]);

    $intent = Stripe::createIntent($cart->calculate(), []);

    expect($intent->id)->not->toBe('PI_CANCELED')
        ->and($intent->status)->not->toBe('canceled');

    // The stale record is corrected and the fresh intent recorded.
    assertDatabaseHas(StripePaymentIntent::class, [
        'intent_id' => 'PI_CANCELED',
        'status' => 'canceled',
    ]);
    assertDatabaseHas(StripePaymentIntent::class, [
        'intent_id' => $intent->id,
        'cart_id' => $cart->id,
    ]);
});

it('hands back a fresh intent from fetchOrCreateIntent when the stored one is dead', function () {
    $cart = CartBuilder::build();

    $cart->paymentIntents()->create([
        'intent_id' => 'PI_CANCELED',
        'status' => 'requires_payment_method',
    ]);

    $intent = Stripe::fetchOrCreateIntent($cart->calculate());

    expect($intent->status)->not->toBe('canceled')
        ->and($intent->id)->not->toBe('PI_CANCELED');
});

it('returns legacy payment intent id stored in cart meta', function () {
    $cart = CartBuilder::build([
        'meta' => [
            'payment_intent' => 'PI_LEGACY_META',
        ],
    ]);

    expect(Stripe::getCartIntentId($cart))->toBe('PI_LEGACY_META');
});

it('prefers legacy meta payment intent over active relation', function () {
    $cart = CartBuilder::build([
        'meta' => [
            'payment_intent' => 'PI_LEGACY_META',
        ],
    ]);

    $cart->paymentIntents()->create([
        'intent_id' => 'PI_RELATION',
        'status' => 'requires_payment_method',
    ]);

    expect(Stripe::getCartIntentId($cart))->toBe('PI_LEGACY_META');
});

it('falls back to active payment intent when no legacy meta', function () {
    $cart = CartBuilder::build();

    $cart->paymentIntents()->create([
        'intent_id' => 'PI_RELATION',
        'status' => 'requires_payment_method',
    ]);

    expect(Stripe::getCartIntentId($cart))->toBe('PI_RELATION');
});

it('keys cart intent reuse by flavour', function () {
    $cart = CartBuilder::build();

    $cart->paymentIntents()->create([
        'intent_id' => 'PI_STANDARD',
        'status' => 'requires_payment_method',
        'flavour' => 'standard',
    ]);
    $cart->paymentIntents()->create([
        'intent_id' => 'PI_HOLD',
        'status' => 'requires_capture',
        'flavour' => 'hold',
    ]);

    expect(Stripe::getCartIntentId($cart))->toBe('PI_STANDARD')
        ->and(Stripe::getCartIntentId($cart, 'hold'))->toBe('PI_HOLD');
});

it('stamps the flavour on freshly minted intents', function () {
    $cart = CartBuilder::build();

    Stripe::createIntent($cart->calculate(), [], 'hold');

    expect($cart->paymentIntents()->first()->flavour)->toBe('hold');
});

it('passes through amounts for standard currencies', function (string $code, int $decimals, int $value) {
    $currency = Currency::factory()->make([
        'code' => $code,
        'decimal_places' => $decimals,
    ]);

    expect(StripeManager::toStripeAmount($value, $currency))->toBe($value);
})->with([
    ['USD', 2, 1148],
    ['JPY', 0, 1000],
    ['KRW', 0, 1000],
    ['BHD', 3, 10234],
]);

it('multiplies HUF/TWD/UGX amounts by 100 when stored as zero-decimal', function (string $code) {
    $currency = Currency::factory()->make([
        'code' => $code,
        'decimal_places' => 0,
    ]);

    expect(StripeManager::toStripeAmount(11480, $currency))->toBe(1148000);
})->with(['HUF', 'TWD', 'UGX']);

it('passes through HUF when stored with two decimal places', function () {
    $currency = Currency::factory()->make([
        'code' => 'HUF',
        'decimal_places' => 2,
    ]);

    expect(StripeManager::toStripeAmount(1148000, $currency))->toBe(1148000);
});

it('is case-insensitive on the currency code', function () {
    $currency = Currency::factory()->make([
        'code' => 'huf',
        'decimal_places' => 0,
    ]);

    expect(StripeManager::toStripeAmount(500, $currency))->toBe(50000);
});

it('normalises amounts for currencies configured with more than 2 decimal places', function () {
    // A $60.0000 cart stored with 4 decimal places is 600000, not 6000.
    $currency = Currency::factory()->make([
        'code' => 'USD',
        'decimal_places' => 4,
    ]);

    expect(StripeManager::toStripeAmount(600000, $currency))->toBe(6000);
});

it('normalises amounts for zero-decimal currencies misconfigured with extra decimal places', function () {
    $currency = Currency::factory()->make([
        'code' => 'JPY',
        'decimal_places' => 2,
    ]);

    expect(StripeManager::toStripeAmount(100000, $currency))->toBe(1000);
});

it('rounds half-unit boundaries exactly when rescaling', function () {
    // 145 at 3dp is 0.145 — binary float division would misround this to 14.
    $currency = Currency::factory()->make([
        'code' => 'USD',
        'decimal_places' => 3,
    ]);

    expect(StripeManager::toStripeAmount(145, $currency))->toBe(15);

    $currency = Currency::factory()->make([
        'code' => 'USD',
        'decimal_places' => 4,
    ]);

    expect(StripeManager::toStripeAmount(1450, $currency))->toBe(15);
});

it('sends three-decimal currency amounts in thousandths', function () {
    $currency = Currency::factory()->make([
        'code' => 'BHD',
        'decimal_places' => 3,
    ]);

    expect(StripeManager::toStripeAmount(60123, $currency))->toBe(60123);

    $currency = Currency::factory()->make([
        'code' => 'BHD',
        'decimal_places' => 2,
    ]);

    expect(StripeManager::toStripeAmount(6012, $currency))->toBe(60120);
});

it('converts Stripe amounts back to the stored scale', function () {
    $currency = Currency::factory()->make([
        'code' => 'USD',
        'decimal_places' => 4,
    ]);

    expect(StripeManager::fromStripeAmount(6000, $currency))->toBe(600000);

    $currency = Currency::factory()->make([
        'code' => 'JPY',
        'decimal_places' => 2,
    ]);

    expect(StripeManager::fromStripeAmount(1000, $currency))->toBe(100000);

    $currency = Currency::factory()->make([
        'code' => 'HUF',
        'decimal_places' => 0,
    ]);

    expect(StripeManager::fromStripeAmount(50000, $currency))->toBe(500);

    // Identity for a correctly-configured two-decimal currency.
    $currency = Currency::factory()->make([
        'code' => 'USD',
        'decimal_places' => 2,
    ]);

    expect(StripeManager::fromStripeAmount(1999, $currency))->toBe(1999);
});

it('creates holds as manual capture intents with incremental authorization requested', function () {
    $cart = CartBuilder::build();
    $mock = Stripe::fake();

    Stripe::createHold($cart->calculate());

    $create = collect($mock->requests)
        ->first(fn ($r) => $r['method'] === 'post' && str_ends_with($r['url'], 'payment_intents'));

    expect($create['params']['capture_method'])->toBe('manual')
        ->and($create['params']['payment_method_options']['card']['request_incremental_authorization'])->toBe('if_available');

    expect($cart->paymentIntents()->first()->flavour)->toBe('hold');
});

it('hands the client secret to the frontend in the client payload', function () {
    Stripe::fake();
    $cart = CartBuilder::build();

    $descriptor = paymentDriver()->createIntent($cart->calculate());

    expect($descriptor->redirectUrl)->toBeNull()
        ->and($descriptor->clientPayload)->toHaveKey('client_secret')
        ->and($descriptor->clientPayload['client_secret'])->not->toBeEmpty();
});

it('recovers the first intent when a create is retried after a lost response', function () {
    $cart = CartBuilder::build();
    $idempotencyKey = fn (MockClient $mock) => collect($mock->requests[0]['headers'])
        ->first(fn ($header) => str_starts_with($header, 'Idempotency-Key: '));

    $timedOut = Stripe::fake()->alwaysThrow();

    expect(fn () => paymentDriver()->createIntent($cart->calculate()))->toThrow(PaymentIntentException::class);

    $retry = Stripe::fake();
    paymentDriver()->createIntent($cart->calculate());

    expect($idempotencyKey($retry))->not->toBeNull()
        ->toBe($idempotencyKey($timedOut));
});

it('throws when a sync cannot bring the intent in line', function () {
    $cart = CartBuilder::build();
    $cart->paymentIntents()->create(['intent_id' => 'PI_STANDARD', 'status' => 'requires_payment_method']);

    Stripe::fake()->alwaysThrow();

    paymentDriver()->syncIntent($cart);
})->throws(PaymentIntentException::class);

it('brings the currency in line when syncing an intent', function () {
    $cart = CartBuilder::build();
    $cart->paymentIntents()->create(['intent_id' => 'PI_STANDARD', 'status' => 'requires_payment_method']);

    $mock = Stripe::fake();
    paymentDriver()->syncIntent($cart);

    $update = collect($mock->requests)
        ->first(fn ($r) => $r['method'] === 'post' && str_contains($r['url'], 'payment_intents/PI_STANDARD'));

    expect($update['params']['currency'])->toBe($cart->currency->code);
});

it('re-prices a reused intent when the cart has changed currency', function () {
    $cart = CartBuilder::build();
    $cart->paymentIntents()->create(['intent_id' => 'PI_HOLD', 'status' => 'requires_payment_method']);

    $mock = Stripe::fake()->next(['currency' => 'jpy', 'status' => 'requires_payment_method']);
    paymentDriver()->createIntent($cart->calculate());

    $update = collect($mock->requests)
        ->first(fn ($r) => $r['method'] === 'post' && str_contains($r['url'], 'payment_intents/PI_HOLD'));

    expect($update)->not->toBeNull()
        ->and($update['params']['currency'])->toBe($cart->currency->code)
        ->and($cart->paymentIntents()->count())->toBe(1);
});

it('replaces an authorised hold when the cart has changed currency', function () {
    $cart = CartBuilder::build();
    $cart->paymentIntents()->create(['intent_id' => 'PI_HOLD', 'status' => 'requires_capture', 'flavour' => 'hold']);

    $mock = Stripe::fake()->next(['currency' => 'jpy']);
    Stripe::createHold($cart->calculate());

    $posts = collect($mock->requests)->where('method', 'post')->pluck('url');

    // Stripe cannot re-price an authorised hold, so it is released, not updated.
    expect($posts->contains(fn ($url) => str_ends_with($url, 'payment_intents/PI_HOLD')))->toBeFalse()
        ->and($posts->contains(fn ($url) => str_ends_with($url, 'payment_intents/PI_HOLD/cancel')))->toBeTrue()
        ->and($posts->last())->toEndWith('payment_intents')
        ->and($cart->paymentIntents()->where('intent_id', 'PI_HOLD')->value('status'))->toBe('canceled')
        ->and($cart->paymentIntents()->where('flavour', 'hold')->count())->toBe(2);
});

it('does not open a second hold when the wrong-currency hold cannot be released', function () {
    $cart = CartBuilder::build();
    $cart->paymentIntents()->create(['intent_id' => 'PI_HOLD', 'status' => 'requires_capture', 'flavour' => 'hold']);
    $cart = $cart->calculate();

    // The retrieve answers, then Stripe becomes unreachable for the cancel.
    $mock = new class extends MockClient
    {
        public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1')
        {
            if ($method === 'post') {
                $this->alwaysThrow();
            }

            return parent::request($method, $absUrl, $headers, $params, $hasFile, $apiMode);
        }
    };
    $mock->next(['currency' => 'jpy']);
    ApiRequestor::setHttpClient($mock);

    expect(fn () => paymentDriver()->createHold($cart))->toThrow(PaymentIntentException::class)
        ->and(collect($mock->requests)->contains(fn ($r) => str_ends_with($r['url'], '/payment_intents')))->toBeFalse()
        ->and($cart->paymentIntents()->count())->toBe(1);
});

/*
| Spec 0077 lifecycle table, one test per cell Stripe reaches.
*/

/**
 * A GBP cart owning one intent of the given flavour, with every mocked
 * response in GBP unless the test overrides it.
 */
function cartOwning(string $reference, string $flavour = 'hold', array $currencyParams = []): Cart
{
    $cart = CartBuilder::build(currencyParams: ['code' => 'GBP', 'decimal_places' => 2, ...$currencyParams]);

    $cart->paymentIntents()->create([
        'intent_id' => $reference,
        'status' => PaymentIntent::STATUS_REQUIRES_CAPTURE,
        'flavour' => $flavour,
    ]);

    return $cart->calculate();
}

function gbp(Cart $cart, int $value): PriceValue
{
    return new PriceValue($value, $cart->currency);
}

function stripeCalls(MockClient $mock, string $urlPart): Collection
{
    return collect($mock->requests)->filter(fn ($r) => str_contains($r['url'], $urlPart));
}

it('returns no snapshot for another cart\'s reference, without asking Stripe', function () {
    $mock = Stripe::fake();
    cartOwning('PI_HOLD');
    $otherCart = Cart::factory()->create(['currency_id' => Currency::first()->id]);

    expect(paymentDriver()->fetchIntent($otherCart, 'PI_HOLD'))->toBeNull()
        ->and($mock->requests)->toBeEmpty();
});

it('refuses to act on another cart\'s reference, without asking Stripe', function (Closure $act) {
    $mock = Stripe::fake();
    $owner = cartOwning('PI_HOLD');
    $otherCart = Cart::factory()->create(['currency_id' => $owner->currency_id]);

    expect(fn () => $act(paymentDriver(), $otherCart))->toThrow(PaymentIntentException::class)
        ->and($mock->requests)->toBeEmpty();
})->with([
    'void' => [fn ($driver, $cart) => $driver->voidIntent($cart, 'PI_HOLD')],
    'refund' => [fn ($driver, $cart) => $driver->refundIntent($cart, 'PI_HOLD', gbp($cart, 100), 'key')],
    'adjust' => [fn ($driver, $cart) => $driver->adjustHold($cart, 'PI_HOLD', gbp($cart, 100))],
    'capture' => [fn ($driver, $cart) => $driver->captureHold($cart, 'PI_HOLD', gbp($cart, 100))],
]);

it('reports a captured intent\'s money as PriceValues', function () {
    $cart = cartOwning('PI_CAPTURE', 'standard');
    Stripe::fake()->next(['currency' => 'gbp']);

    $snapshot = paymentDriver()->fetchIntent($cart, 'PI_CAPTURE');

    expect($snapshot->status)->toBe(PaymentIntentStatus::Captured)
        ->and($snapshot->amount->value)->toBe(2000)
        ->and($snapshot->amount->resolveCurrency()->code)->toBe('GBP')
        ->and($snapshot->captured->value)->toBe(2000)
        ->and($snapshot->refunded->value)->toBe(0);
});

it('reports amounts on the scale Lunar stores, not Stripe\'s', function () {
    // GBP stored with three decimal places: Stripe's 2000 pence is 20000.
    $cart = cartOwning('PI_CAPTURE', 'standard', ['decimal_places' => 3]);
    Stripe::fake()->next(['currency' => 'gbp']);

    expect(paymentDriver()->fetchIntent($cart, 'PI_CAPTURE')->captured->value)->toBe(20000);
});

it('reports an authorised hold with nothing captured and its wallet', function () {
    $cart = cartOwning('PI_HOLD');
    Stripe::fake()->next(['currency' => 'gbp']);

    $snapshot = paymentDriver()->fetchIntent($cart, 'PI_HOLD');

    expect($snapshot->status)->toBe(PaymentIntentStatus::RequiresCapture)
        ->and($snapshot->amount->value)->toBe(2000)
        ->and($snapshot->captured->value)->toBe(0)
        ->and($snapshot->walletLabel)->toBe('Apple Pay');
});

it('maps Stripe statuses onto the lifecycle statuses', function (string $stripeStatus, PaymentIntentStatus $expected) {
    $cart = cartOwning('PI_HOLD');
    Stripe::fake()->next(['currency' => 'gbp', 'status' => $stripeStatus]);

    expect(paymentDriver()->fetchIntent($cart, 'PI_HOLD')->status)->toBe($expected);
})->with([
    // A declined card drops back here and can be retried, so it is not Failed.
    'requires_payment_method' => ['requires_payment_method', PaymentIntentStatus::RequiresAction],
    'requires_confirmation' => ['requires_confirmation', PaymentIntentStatus::RequiresAction],
    'requires_action' => ['requires_action', PaymentIntentStatus::RequiresAction],
    'processing' => ['processing', PaymentIntentStatus::Processing],
    'requires_capture' => ['requires_capture', PaymentIntentStatus::RequiresCapture],
    'succeeded' => ['succeeded', PaymentIntentStatus::Captured],
    'canceled' => ['canceled', PaymentIntentStatus::Voided],
]);

it('throws rather than guess a status Stripe has added since', function () {
    $cart = cartOwning('PI_HOLD');
    Stripe::fake()->next(['currency' => 'gbp', 'status' => 'something_new']);

    paymentDriver()->fetchIntent($cart, 'PI_HOLD');
})->throws(PaymentIntentException::class);

it('throws when Stripe cannot report the intent', function () {
    $cart = cartOwning('PI_HOLD');
    Stripe::fake()->alwaysThrow();

    paymentDriver()->fetchIntent($cart, 'PI_HOLD');
})->throws(PaymentIntentException::class);

it('voids a live intent at Stripe and records it', function (string $stripeStatus) {
    $cart = cartOwning('PI_HOLD');
    $mock = Stripe::fake()->next(['currency' => 'gbp', 'status' => $stripeStatus]);

    paymentDriver()->voidIntent($cart, 'PI_HOLD');

    expect(stripeCalls($mock, 'PI_HOLD/cancel'))->toHaveCount(1)
        ->and($cart->paymentIntents()->value('status'))->toBe('canceled');
})->with(['requires_action' => 'requires_payment_method', 'requires_capture' => 'requires_capture']);

it('treats voiding a voided intent as done', function () {
    $cart = cartOwning('PI_HOLD');
    $mock = Stripe::fake()->next(['currency' => 'gbp', 'status' => 'canceled']);

    paymentDriver()->voidIntent($cart, 'PI_HOLD');

    expect(stripeCalls($mock, '/cancel'))->toBeEmpty();
});

it('refuses to void a processing or captured intent', function (string $stripeStatus) {
    $cart = cartOwning('PI_HOLD');
    $mock = Stripe::fake()->next(['currency' => 'gbp', 'status' => $stripeStatus]);

    expect(fn () => paymentDriver()->voidIntent($cart, 'PI_HOLD'))->toThrow(PaymentIntentException::class)
        ->and(stripeCalls($mock, '/cancel'))->toBeEmpty();
})->with(['processing', 'succeeded']);

it('throws when Stripe cannot confirm a void', function () {
    $cart = cartOwning('PI_HOLD');
    Stripe::fake()->alwaysThrow();

    paymentDriver()->voidIntent($cart, 'PI_HOLD');
})->throws(PaymentIntentException::class);

it('stops syncing an intent once it is voided', function () {
    $cart = cartOwning('PI_HOLD', 'standard');
    $mock = Stripe::fake()->next(['currency' => 'gbp', 'status' => 'requires_payment_method']);

    paymentDriver()->voidIntent($cart, 'PI_HOLD');
    $requestsAfterVoid = count($mock->requests);
    paymentDriver()->syncIntent($cart);

    expect($mock->requests)->toHaveCount($requestsAfterVoid);
});

it('refunds a captured intent in Stripe\'s scale and reports the refund', function () {
    $cart = cartOwning('PI_CAPTURE', 'standard');
    $mock = Stripe::fake()->next(['currency' => 'gbp']);

    $refund = paymentDriver()->refundIntent($cart, 'PI_CAPTURE', gbp($cart, 1500), 'idem-key-1');

    $call = collect($mock->requests)->first(fn ($r) => $r['method'] === 'post' && str_contains($r['url'], 'refunds'));

    expect($refund->success)->toBeTrue()
        ->and($refund->pending)->toBeFalse()
        ->and($refund->transaction)->toBeNull()
        ->and($refund->reference)->not->toBeEmpty()
        ->and($call['params'])->toMatchArray(['payment_intent' => 'PI_CAPTURE', 'amount' => 1500])
        ->and($call['headers'])->toContain('Idempotency-Key: idem-key-1');
});

it('marks a refund Stripe has not settled as pending', function () {
    $cart = cartOwning('PI_CAPTURE', 'standard');
    Stripe::fake()->next(['currency' => 'gbp', 'refund_status' => 'pending']);

    expect(paymentDriver()->refundIntent($cart, 'PI_CAPTURE', gbp($cart, 1500), 'key')->pending)->toBeTrue();
});

it('throws when Stripe fails the refund outright', function () {
    $cart = cartOwning('PI_CAPTURE', 'standard');
    Stripe::fake()->next(['currency' => 'gbp', 'refund_status' => 'failed']);

    paymentDriver()->refundIntent($cart, 'PI_CAPTURE', gbp($cart, 1500), 'key');
})->throws(PaymentIntentException::class);

it('refuses to refund more than is left on the intent', function () {
    $cart = cartOwning('PI_CAPTURE', 'standard');
    $mock = Stripe::fake()->next(['currency' => 'gbp']);

    expect(fn () => paymentDriver()->refundIntent($cart, 'PI_CAPTURE', gbp($cart, 2500), 'key'))
        ->toThrow(PaymentIntentException::class)
        ->and(stripeCalls($mock, 'refunds'))->toBeEmpty();
});

it('refuses to refund an intent that is not captured', function () {
    $cart = cartOwning('PI_HOLD');
    $mock = Stripe::fake()->next(['currency' => 'gbp']);

    expect(fn () => paymentDriver()->refundIntent($cart, 'PI_HOLD', gbp($cart, 500), 'key'))
        ->toThrow(PaymentIntentException::class)
        ->and(stripeCalls($mock, 'refunds'))->toBeEmpty();
});

it('refuses an amount in another currency from the intent', function () {
    $cart = cartOwning('PI_CAPTURE', 'standard');
    $mock = Stripe::fake()->next(['currency' => 'gbp']);
    $euros = new PriceValue(500, Currency::factory()->make(['code' => 'EUR']));

    expect(fn () => paymentDriver()->refundIntent($cart, 'PI_CAPTURE', $euros, 'key'))
        ->toThrow(PaymentIntentException::class)
        ->and(stripeCalls($mock, 'refunds'))->toBeEmpty();
});

it('throws when Stripe cannot confirm a refund', function () {
    $cart = cartOwning('PI_CAPTURE', 'standard');
    Stripe::fake()->alwaysThrow();

    paymentDriver()->refundIntent($cart, 'PI_CAPTURE', gbp($cart, 1500), 'key');
})->throws(PaymentIntentException::class);

it('reports a hold that already covers the amount as Ok without asking for more', function () {
    $cart = cartOwning('PI_HOLD');
    $mock = Stripe::fake()->next(['currency' => 'gbp']);

    expect(paymentDriver()->adjustHold($cart, 'PI_HOLD', gbp($cart, 1500)))->toBe(HoldAdjustment::Ok)
        ->and(stripeCalls($mock, 'increment_authorization'))->toBeEmpty();
});

it('raises a hold by incremental authorisation where the rail supports it', function () {
    $cart = cartOwning('PI_HOLD');
    $mock = Stripe::fake()->next(['currency' => 'gbp']);

    expect(paymentDriver()->adjustHold($cart, 'PI_HOLD', gbp($cart, 2600)))->toBe(HoldAdjustment::Ok)
        ->and(stripeCalls($mock, 'PI_HOLD/increment_authorization')->first()['params']['amount'])->toBe(2600);
});

it('asks for a new authorisation when the rail cannot raise the hold', function () {
    $cart = cartOwning('PI_HOLD_NOINC');
    Stripe::fake()->next(['currency' => 'gbp']);

    expect(paymentDriver()->adjustHold($cart, 'PI_HOLD_NOINC', gbp($cart, 2600)))
        ->toBe(HoldAdjustment::NeedsReauthorization);
});

it('refuses to adjust a hold that is not awaiting capture', function () {
    $cart = cartOwning('PI_HOLD');
    Stripe::fake()->next(['currency' => 'gbp', 'status' => 'succeeded']);

    paymentDriver()->adjustHold($cart, 'PI_HOLD', gbp($cart, 1500));
})->throws(PaymentIntentException::class);

it('captures a hold for the final amount and records it', function () {
    $cart = cartOwning('PI_HOLD');
    $mock = Stripe::fake()->next(['currency' => 'gbp']);

    paymentDriver()->captureHold($cart, 'PI_HOLD', gbp($cart, 1800));

    expect(stripeCalls($mock, 'PI_HOLD/capture')->first()['params']['amount_to_capture'])->toBe(1800)
        ->and($cart->paymentIntents()->value('status'))->toBe('succeeded');
});

it('refuses to capture more than the hold authorised rather than clamp', function () {
    $cart = cartOwning('PI_HOLD');
    $mock = Stripe::fake()->next(['currency' => 'gbp']);

    expect(fn () => paymentDriver()->captureHold($cart, 'PI_HOLD', gbp($cart, 2500)))->toThrow(PaymentIntentException::class)
        ->and(stripeCalls($mock, '/capture'))->toBeEmpty();
});

it('treats a repeat capture at the captured amount as done', function () {
    $cart = cartOwning('PI_CAPTURE');
    $mock = Stripe::fake()->next(['currency' => 'gbp']);

    paymentDriver()->captureHold($cart, 'PI_CAPTURE', gbp($cart, 2000));

    expect(stripeCalls($mock, '/capture'))->toBeEmpty();
});

it('refuses a repeat capture at a different amount', function () {
    $cart = cartOwning('PI_CAPTURE');
    $mock = Stripe::fake()->next(['currency' => 'gbp']);

    expect(fn () => paymentDriver()->captureHold($cart, 'PI_CAPTURE', gbp($cart, 1800)))->toThrow(PaymentIntentException::class)
        ->and(stripeCalls($mock, '/capture'))->toBeEmpty();
});

it('refuses to capture a hold that is not authorised yet', function () {
    $cart = cartOwning('PI_HOLD');
    Stripe::fake()->next(['currency' => 'gbp', 'status' => 'requires_payment_method']);

    paymentDriver()->captureHold($cart, 'PI_HOLD', gbp($cart, 1800));
})->throws(PaymentIntentException::class);

it('throws when Stripe cannot confirm a capture', function () {
    $cart = cartOwning('PI_HOLD');
    Stripe::fake()->alwaysThrow();

    paymentDriver()->captureHold($cart, 'PI_HOLD', gbp($cart, 1800));
})->throws(PaymentIntentException::class);

it('creates another intent once the last one is captured', function () {
    $cart = cartOwning('PI_CAPTURE', 'standard');
    $mock = Stripe::fake()->next(['currency' => 'gbp']);

    paymentDriver()->createIntent($cart);

    expect(collect($mock->requests)->contains(fn ($r) => $r['method'] === 'post' && str_ends_with($r['url'], '/payment_intents')))->toBeTrue()
        ->and($cart->paymentIntents()->count())->toBe(2);
});

it('authorises again after a short hold is voided, under a new key', function () {
    $cart = cartOwning('PI_HOLD');
    $mock = Stripe::fake()->next(['currency' => 'gbp']);

    paymentDriver()->voidIntent($cart, 'PI_HOLD');
    paymentDriver()->createHold($cart);

    $create = collect($mock->requests)->last(fn ($r) => $r['method'] === 'post' && str_ends_with($r['url'], '/payment_intents'));
    $key = collect($create['headers'])->first(fn ($header) => str_starts_with($header, 'Idempotency-Key: '));

    expect($key)->toContain("lunar-cart-{$cart->id}-hold-1-");
});
