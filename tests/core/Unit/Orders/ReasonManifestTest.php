<?php

use Lunar\Core\Facades\CancelReasons;
use Lunar\Core\Facades\HoldReasons;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

it('exposes the default hold reasons', function () {
    expect(HoldReasons::all())
        ->toHaveKeys(['awaiting-payment', 'out-of-stock', 'incorrect-address', 'high-risk', 'other'])
        ->and(HoldReasons::all()['out-of-stock'])->toBe('Inventory out of stock');
});

it('exposes the default cancel reasons', function () {
    expect(CancelReasons::all())
        ->toHaveKeys(['customer', 'items-unavailable', 'fraud', 'declined', 'other'])
        ->and(CancelReasons::all()['items-unavailable'])->toBe('Items unavailable');
});

it('resolves a reason label, falling back to the raw key', function () {
    expect(HoldReasons::label('out-of-stock'))->toBe('Inventory out of stock')
        ->and(HoldReasons::label('made-up-key'))->toBe('made-up-key')
        ->and(HoldReasons::label(null))->toBeNull()
        ->and(HoldReasons::label(''))->toBeNull();
});

it('replaces the whole reason set via the override seam', function () {
    CancelReasons::set(['gift' => 'Unwanted gift']);

    expect(CancelReasons::all())->toBe(['gift' => 'Unwanted gift'])
        ->and(CancelReasons::label('gift'))->toBe('Unwanted gift')
        ->and(CancelReasons::label('fraud'))->toBe('fraud');
});

it('adds or relabels a single reason while retaining the defaults', function () {
    HoldReasons::add('weather', 'Severe weather delay');

    expect(HoldReasons::label('weather'))->toBe('Severe weather delay')
        ->and(HoldReasons::all())->toHaveKey('out-of-stock');
});
