<?php

use Lunar\Core\DiscountTypes\BuyXGetY;
use Lunar\Core\DiscountTypes\FixedAmountOff;
use Lunar\Core\DiscountTypes\PercentageOff;
use Lunar\Core\Models\Currency;
use Lunar\Panel\Support\DiscountDataSchema;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    // A two-decimal and a zero-decimal currency, so every scaling assertion
    // below proves the currency's own decimal places are used rather than a
    // hardcoded factor of 100.
    Currency::factory()->create(['code' => 'GBP', 'decimal_places' => 2, 'default' => true, 'enabled' => true]);
    Currency::factory()->create(['code' => 'JPY', 'decimal_places' => 0, 'default' => false, 'enabled' => true]);

    $this->schema = app(DiscountDataSchema::class);
});

test('percentage off round trips untouched', function () {
    $stored = $this->schema->toStorage(PercentageOff::class, ['percentage' => 15]);

    expect($stored['percentage'])->toBe(15.0);
    expect($this->schema->toForm(PercentageOff::class, $stored)['percentage'])->toBe(15.0);
});

test('fixed amount off scales through each currency decimal places', function () {
    $stored = $this->schema->toStorage(FixedAmountOff::class, [
        'amounts' => ['GBP' => 12.34, 'JPY' => 500],
    ]);

    expect($stored['amounts'])->toBe(['GBP' => 1234, 'JPY' => 500]);

    expect($this->schema->toForm(FixedAmountOff::class, $stored)['amounts'])
        ->toBe(['GBP' => 12.34, 'JPY' => 500.0]);
});

test('a blank fixed amount stores no entry for that currency', function () {
    $stored = $this->schema->toStorage(FixedAmountOff::class, [
        'amounts' => ['GBP' => 10, 'JPY' => ''],
    ]);

    expect($stored['amounts'])->toBe(['GBP' => 1000]);
});

test('buy x get y normalises quantities and an uncapped reward', function () {
    $stored = $this->schema->toStorage(BuyXGetY::class, [
        'min_qty' => '3',
        'reward_qty' => '1',
        'max_reward_qty' => '',
        'automatically_add_rewards' => '1',
    ]);

    expect($stored)->toBe([
        'min_qty' => 3,
        'reward_qty' => 1,
        'max_reward_qty' => null,
        'automatically_add_rewards' => true,
    ]);
});

test('the minimum spend condition scales per currency', function () {
    $stored = $this->schema->toStorage(PercentageOff::class, [
        'percentage' => 10,
        'min_prices' => ['GBP' => 50, 'JPY' => 5000],
    ]);

    expect($stored['min_prices'])->toBe(['GBP' => 5000, 'JPY' => 5000]);

    expect($this->schema->toForm(PercentageOff::class, $stored)['min_prices'])
        ->toBe(['GBP' => 50.0, 'JPY' => 5000.0]);
});

test('the minimum spend survives a type form that returns only its own keys', function () {
    // PercentageOffForm::toStorage() returns just `percentage`. The condition is
    // core's, read for every type, so composing it here is what keeps it.
    $stored = $this->schema->toStorage(PercentageOff::class, [
        'percentage' => 10,
        'min_prices' => ['GBP' => 25],
    ]);

    expect($stored)->toHaveKey('min_prices');
    expect($stored['min_prices'])->toBe(['GBP' => 2500]);
});

test('clearing every minimum removes the condition rather than storing zeroes', function () {
    $stored = $this->schema->toStorage(PercentageOff::class, [
        'percentage' => 10,
        'min_prices' => ['GBP' => '', 'JPY' => ''],
    ]);

    expect($stored)->not->toHaveKey('min_prices');
});

test('rules cover the type payload and the shared conditions', function () {
    $rules = $this->schema->rules(FixedAmountOff::class);

    expect($rules)->toHaveKeys(['amounts', 'amounts.GBP', 'amounts.JPY', 'min_prices', 'min_prices.GBP', 'min_prices.JPY']);
});

test('each type summarises its own effect', function () {
    $currency = Currency::getDefault();

    expect($this->schema->summary(PercentageOff::class, ['percentage' => 15], $currency))->toBe('15% off');
    expect($this->schema->summary(BuyXGetY::class, ['min_qty' => 2, 'reward_qty' => 1], $currency))->toBe('Buy 2, get 1');
    expect($this->schema->summary(FixedAmountOff::class, ['amounts' => ['GBP' => 2000]], $currency))->toContain('20.00');
});

test('a type that cannot summarise itself says nothing', function () {
    $currency = Currency::getDefault();

    expect($this->schema->summary(PercentageOff::class, [], $currency))->toBeNull();
    expect($this->schema->summary(BuyXGetY::class, [], $currency))->toBeNull();
    expect($this->schema->summary('Acme\\Discounts\\Removed', ['anything' => 1], $currency))->toBeNull();
});
