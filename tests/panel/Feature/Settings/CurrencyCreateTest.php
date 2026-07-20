<?php

use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');
});

test('a currency can be created', function () {
    $this->post(route('panel.settings.currencies.store'), [
        'code' => 'gbp',
        'name' => 'Pound Sterling',
        'exchange_rate' => '1',
        'decimal_places' => '2',
        'enabled' => true,
    ])->assertRedirect(route('panel.settings.currencies.index'))
        ->assertSessionHas('success');

    $currency = Currency::where('code', 'GBP')->first();

    expect($currency)->not->toBeNull();
    expect($currency->name)->toBe('Pound Sterling');
    expect((float) $currency->exchange_rate)->toBe(1.0);
    expect($currency->decimal_places)->toBe(2);
    expect($currency->enabled)->toBeTrue();
    expect($currency->default)->toBeFalse();
});

test('creating a second currency as default un-defaults the first', function () {
    $first = Currency::factory()->create(['code' => 'GBP', 'default' => true]);

    $this->post(route('panel.settings.currencies.store'), [
        'code' => 'EUR',
        'name' => 'Euro',
        'exchange_rate' => '1.2',
        'decimal_places' => '2',
        'default' => true,
    ])->assertRedirect(route('panel.settings.currencies.index'));

    $second = Currency::where('code', 'EUR')->first();

    expect($first->fresh()->default)->toBeFalse();
    expect($second->default)->toBeTrue();
    expect($second->enabled)->toBeTrue();
    expect(Currency::where('default', true)->count())->toBe(1);
});

test('code and name are required', function () {
    $this->post(route('panel.settings.currencies.store'), [
        'exchange_rate' => '1',
        'decimal_places' => '2',
    ])->assertSessionHasErrors(['code', 'name']);

    expect(Currency::count())->toBe(0);
});

test('code must be a unique three-letter code', function () {
    Currency::factory()->create(['code' => 'GBP']);

    $this->post(route('panel.settings.currencies.store'), [
        'code' => 'GBP',
        'name' => 'Pound Sterling',
        'exchange_rate' => '1',
        'decimal_places' => '2',
    ])->assertSessionHasErrors('code');

    $this->post(route('panel.settings.currencies.store'), [
        'code' => 'POUND',
        'name' => 'Pound Sterling',
        'exchange_rate' => '1',
        'decimal_places' => '2',
    ])->assertSessionHasErrors('code');
});

test('exchange rate must be a positive number', function () {
    $this->post(route('panel.settings.currencies.store'), [
        'code' => 'EUR',
        'name' => 'Euro',
        'exchange_rate' => '0',
        'decimal_places' => '2',
    ])->assertSessionHasErrors('exchange_rate');
});
