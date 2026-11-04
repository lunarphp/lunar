<?php

use Lunar\Core\Contracts\CreatesPaymentIntents;
use Lunar\Core\Contracts\PaymentType;
use Lunar\Core\Contracts\ReconcilesPaymentIntents;
use Lunar\Core\Contracts\SupportsPaymentHolds;
use Lunar\Core\Contracts\SyncsPaymentIntents;
use Lunar\Core\DataObjects\PaymentIntentDescriptor;
use Lunar\Core\DataObjects\PaymentIntentSnapshot;
use Lunar\Core\DataObjects\PaymentRefund;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Enums\HoldAdjustment;
use Lunar\Core\Enums\PaymentIntentStatus;
use Lunar\Core\Exceptions\LunarException;
use Lunar\Core\Exceptions\PaymentIntentException;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Currency;
use Lunar\Core\PaymentTypes\OfflinePayment;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

it('keeps the capabilities off the PaymentType contract', function () {
    // The capabilities are opt-in: a driver built against PaymentType alone
    // must keep working without implementing any of them.
    $required = class_implements(OfflinePayment::class);

    expect($required)->toHaveKey(PaymentType::class)
        ->and($required)->not->toHaveKey(CreatesPaymentIntents::class)
        ->and($required)->not->toHaveKey(SyncsPaymentIntents::class)
        ->and($required)->not->toHaveKey(ReconcilesPaymentIntents::class)
        ->and($required)->not->toHaveKey(SupportsPaymentHolds::class);
});

it('makes every intent-creating capability reconcilable', function () {
    // An intent that cannot be reported or voided cannot be reconciled, and a
    // sync without a create has nothing to sync. The chain makes those
    // combinations unrepresentable rather than leaving a fallback rule.
    expect(is_subclass_of(SyncsPaymentIntents::class, CreatesPaymentIntents::class))->toBeTrue()
        ->and(is_subclass_of(CreatesPaymentIntents::class, ReconcilesPaymentIntents::class))->toBeTrue()
        ->and(is_subclass_of(SupportsPaymentHolds::class, ReconcilesPaymentIntents::class))->toBeTrue();
});

it('checks ownership against the cart on every method that takes a reference', function () {
    // References often come from the client. Taking the cart puts the
    // ownership check in the driver, on every method, so no caller can skip it.
    $methods = collect([ReconcilesPaymentIntents::class, SupportsPaymentHolds::class])
        ->flatMap(fn (string $contract) => (new ReflectionClass($contract))->getMethods())
        ->filter(fn (ReflectionMethod $method) => collect($method->getParameters())->contains(fn ($p) => $p->getName() === 'reference'));

    expect($methods)->not->toBeEmpty();

    foreach ($methods as $method) {
        $parameters = $method->getParameters();

        expect((string) $parameters[0]->getType())->toBe(Cart::class, $method->getName())
            ->and($parameters[1]->getName())->toBe('reference', $method->getName());
    }
});

// "MUST throw" in prose is not a contract a caller can catch. Every method on
// the four capabilities names PaymentIntentException, so a consumer catches
// that rather than \Throwable and cannot confuse a gateway that went quiet with
// a bug in its own code. Create and sync are not exempt: a failed create may
// still have made an object, and a failed sync must keep confirmation blocked.
it('names a catchable exception on every capability method', function (string $contract) {
    foreach ((new ReflectionClass($contract))->getMethods() as $method) {
        expect($method->getDocComment())->toContain('@throws PaymentIntentException');
    }
})->with([
    CreatesPaymentIntents::class,
    SyncsPaymentIntents::class,
    ReconcilesPaymentIntents::class,
    SupportsPaymentHolds::class,
]);

it('reports the money on an intent so a no-order refund can be sized', function () {
    // The cart may have changed since the customer confirmed, so a refund is
    // sized from what the gateway says was captured and already refunded.
    expect((string) (new ReflectionMethod(ReconcilesPaymentIntents::class, 'fetchIntent'))->getReturnType())
        ->toBe('?'.PaymentIntentSnapshot::class);
});

it('reports intent money as PriceValues in the intent currency', function () {
    // PriceValue carries its currency, so a comparison with a cart total in
    // another currency throws instead of passing on a bare integer.
    $currency = Currency::factory()->make(['code' => 'GBP']);
    $price = fn (int $value) => new PriceValue($value, $currency);

    $snapshot = new PaymentIntentSnapshot(
        PaymentIntentStatus::Captured, $price(2000), $price(2000), $price(500), walletLabel: 'Apple Pay',
    );

    expect($snapshot->captured->subtract($snapshot->refunded)->value)->toBe(1500)
        ->and($snapshot->walletLabel)->toBe('Apple Pay');
});
it('hands a redirect or an opaque client payload to the frontend', function () {
    $hosted = new PaymentIntentDescriptor('tr_123', redirectUrl: 'https://pay.example/tr_123');
    $inPage = new PaymentIntentDescriptor('pi_123', clientPayload: ['client_secret' => 'pi_123_secret']);
    $referenceOnly = new PaymentIntentDescriptor('ORDER-123');

    expect($hosted->clientPayload)->toBe([])
        ->and($inPage->redirectUrl)->toBeNull()
        ->and($inPage->clientPayload)->toBe(['client_secret' => 'pi_123_secret'])
        ->and($referenceOnly->redirectUrl)->toBeNull()
        ->and($referenceOnly->clientPayload)->toBe([]);
});

it('gives the intent exception a catchable Lunar base', function () {
    // Consumers catch PaymentIntentException specifically; anything catching
    // LunarException broadly must keep seeing it too.
    expect(is_subclass_of(PaymentIntentException::class, LunarException::class))->toBeTrue()
        ->and(is_subclass_of(PaymentIntentException::class, Throwable::class))->toBeTrue();
});

it('pins the wire values of the intent statuses', function () {
    // Drivers map their own gateway statuses onto these, and consumers
    // persist them, so the backed values are contract.
    expect(array_map(fn (PaymentIntentStatus $case): string => $case->value, PaymentIntentStatus::cases()))
        ->toBe(['requires_action', 'processing', 'requires_capture', 'captured', 'voided', 'failed']);
});

it('pins the wire values of the hold adjustment outcomes', function () {
    expect(array_map(fn (HoldAdjustment $case): string => $case->value, HoldAdjustment::cases()))
        ->toBe(['ok', 'needs_reauthorization']);
});

it('leaves existing PaymentRefund construction untouched', function () {
    // The new reference and pending fields are trailing and optional, so
    // third-party drivers constructing a PaymentRefund positionally keep
    // working.
    $refund = new PaymentRefund(true, 'Refunded');

    expect($refund->success)->toBeTrue()
        ->and($refund->message)->toBe('Refunded')
        ->and($refund->transaction)->toBeNull()
        ->and($refund->reference)->toBeNull()
        ->and($refund->pending)->toBeFalse();
});

it('carries a gateway refund reference when there is no transaction to hold it', function () {
    $refund = new PaymentRefund(success: true, reference: 're_gateway_123');

    expect($refund->reference)->toBe('re_gateway_123')
        ->and($refund->transaction)->toBeNull();
});

it('marks a refund the gateway accepted but has not settled', function () {
    $refund = new PaymentRefund(success: true, reference: 're_gateway_123', pending: true);

    expect($refund->success)->toBeTrue()
        ->and($refund->pending)->toBeTrue();
});
