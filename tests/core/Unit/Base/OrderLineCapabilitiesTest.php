<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Base\OrderLineCapabilitiesInterface;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create([
        'default' => true,
        'code' => 'en',
    ]);

    Currency::factory()->create([
        'default' => true,
        'code' => 'USD',
        'decimal_places' => 2,
        'exchange_rate' => 1,
    ]);
});

it('returns conservative default order line capabilities', function () {
    $currency = Currency::getDefault();
    $placedOrder = Order::factory()->create([
        'currency_code' => $currency->code,
        'placed_at' => now(),
    ]);

    $physicalLine = OrderLine::factory()->create([
        'order_id' => $placedOrder->id,
        'type' => 'physical',
        'total' => 1000,
    ]);

    $digitalLine = OrderLine::factory()->create([
        'order_id' => $placedOrder->id,
        'type' => 'digital',
        'total' => 800,
    ]);

    $shippingLine = OrderLine::factory()->create([
        'order_id' => $placedOrder->id,
        'type' => 'shipping',
        'total' => 300,
    ]);

    $resolver = app(OrderLineCapabilitiesInterface::class);

    expect($resolver->isRefundable($physicalLine))->toBeTrue()
        ->and($resolver->isCancellable($physicalLine))->toBeFalse()
        ->and($resolver->requiresPhysicalReturn($physicalLine))->toBeTrue()
        ->and($resolver->createsEntitlement($physicalLine))->toBeFalse()
        ->and($resolver->supportsEndOfTerm($physicalLine))->toBeFalse()
        ->and($resolver->allowsAccountCredit($physicalLine))->toBeTrue();

    expect($resolver->isRefundable($digitalLine))->toBeTrue()
        ->and($resolver->isCancellable($digitalLine))->toBeTrue()
        ->and($resolver->requiresPhysicalReturn($digitalLine))->toBeFalse()
        ->and($resolver->createsEntitlement($digitalLine))->toBeTrue()
        ->and($resolver->supportsEndOfTerm($digitalLine))->toBeFalse()
        ->and($resolver->allowsAccountCredit($digitalLine))->toBeTrue();

    expect($resolver->isRefundable($shippingLine))->toBeFalse()
        ->and($resolver->isCancellable($shippingLine))->toBeFalse()
        ->and($resolver->requiresPhysicalReturn($shippingLine))->toBeFalse()
        ->and($resolver->createsEntitlement($shippingLine))->toBeFalse()
        ->and($resolver->supportsEndOfTerm($shippingLine))->toBeFalse()
        ->and($resolver->allowsAccountCredit($shippingLine))->toBeFalse();
});

it('allows packages to replace the capability resolver binding', function () {
    $line = OrderLine::factory()->create();

    app()->bind(OrderLineCapabilitiesInterface::class, fn () => new class implements OrderLineCapabilitiesInterface
    {
        public function isRefundable(OrderLine $orderLine): bool
        {
            return false;
        }

        public function isCancellable(OrderLine $orderLine): bool
        {
            return true;
        }

        public function requiresPhysicalReturn(OrderLine $orderLine): bool
        {
            return false;
        }

        public function createsEntitlement(OrderLine $orderLine): bool
        {
            return true;
        }

        public function supportsEndOfTerm(OrderLine $orderLine): bool
        {
            return true;
        }

        public function allowsAccountCredit(OrderLine $orderLine): bool
        {
            return false;
        }
    });

    $resolver = app(OrderLineCapabilitiesInterface::class);

    expect($resolver->isRefundable($line))->toBeFalse()
        ->and($resolver->isCancellable($line))->toBeTrue()
        ->and($resolver->createsEntitlement($line))->toBeTrue()
        ->and($resolver->supportsEndOfTerm($line))->toBeTrue()
        ->and($resolver->allowsAccountCredit($line))->toBeFalse();
});

it('does not expose refundable or return capabilities for unplaced lines', function () {
    $draftOrder = Order::factory()->create([
        'placed_at' => null,
    ]);

    $line = OrderLine::factory()->create([
        'order_id' => $draftOrder->id,
        'type' => 'physical',
        'total' => 1000,
    ]);

    $resolver = app(OrderLineCapabilitiesInterface::class);

    expect($resolver->isRefundable($line))->toBeFalse()
        ->and($resolver->isCancellable($line))->toBeFalse()
        ->and($resolver->requiresPhysicalReturn($line))->toBeFalse()
        ->and($resolver->createsEntitlement($line))->toBeFalse()
        ->and($resolver->allowsAccountCredit($line))->toBeFalse();
});
