<?php

use Lunar\Core\Contracts\CreatesPaymentIntents;
use Lunar\Core\Contracts\PaymentType;
use Lunar\Core\Contracts\SupportsPaymentHolds;
use Lunar\Core\Contracts\SupportsPaymentIntents;
use Lunar\Core\Contracts\SyncsPaymentIntents;
use Lunar\Core\DataObjects\PaymentRefund;
use Lunar\Core\Enums\HoldAdjustment;
use Lunar\Core\Enums\PaymentIntentStatus;
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
        ->and($required)->not->toHaveKey(SupportsPaymentIntents::class)
        ->and($required)->not->toHaveKey(SupportsPaymentHolds::class);
});

it('guarantees a hold-capable driver can also void', function () {
    // Releasing a hold is voidIntent(), so the extension makes "has holds"
    // imply "can void" at the type level. A caller that has checked for
    // SupportsPaymentHolds must never hit an undefined method on the
    // money-back path.
    expect(is_subclass_of(SupportsPaymentHolds::class, SupportsPaymentIntents::class))->toBeTrue();

    $hold = new ReflectionClass(SupportsPaymentHolds::class);

    expect($hold->hasMethod('voidIntent'))->toBeTrue()
        ->and($hold->hasMethod('refundIntent'))->toBeTrue()
        ->and($hold->hasMethod('fetchIntent'))->toBeTrue();
});

it('pins the wire values of the intent statuses', function () {
    // Drivers map their own gateway statuses onto these, and consumers
    // persist them, so the backed values are contract.
    expect(array_map(fn (PaymentIntentStatus $case): string => $case->value, PaymentIntentStatus::cases()))
        ->toBe(['pending', 'requires_capture', 'captured', 'voided', 'failed']);
});

it('pins the wire values of the hold adjustment outcomes', function () {
    expect(array_map(fn (HoldAdjustment $case): string => $case->value, HoldAdjustment::cases()))
        ->toBe(['ok', 'needs_reauthorization']);
});

it('leaves existing PaymentRefund construction untouched', function () {
    // The new reference field is trailing and optional, so third-party
    // drivers constructing a PaymentRefund positionally keep working.
    $refund = new PaymentRefund(true, 'Refunded');

    expect($refund->success)->toBeTrue()
        ->and($refund->message)->toBe('Refunded')
        ->and($refund->transaction)->toBeNull()
        ->and($refund->reference)->toBeNull();
});

it('carries a gateway refund reference when there is no transaction to hold it', function () {
    $refund = new PaymentRefund(success: true, reference: 're_gateway_123');

    expect($refund->reference)->toBe('re_gateway_123')
        ->and($refund->transaction)->toBeNull();
});
