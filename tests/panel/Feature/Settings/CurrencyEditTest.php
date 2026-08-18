<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');
});

test('the currency edit screen renders with the currency data', function () {
    $currency = Currency::factory()->create([
        'code' => 'EUR',
        'name' => 'Euro',
        'exchange_rate' => 1.2,
        'decimal_places' => 2,
        'enabled' => true,
        'default' => false,
    ]);

    $this->get(route('panel.settings.currencies.edit', $currency))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/currencies/Edit')
            ->where('currency.id', $currency->id)
            ->where('currency.code', 'EUR')
            ->where('currency.name', 'Euro')
            ->where('currency.decimal_places', 2)
            ->where('currency.enabled', true)
            ->where('currency.default', false)
            ->whereType('currency.default', 'boolean')
            ->where('hasPrices', false)
            ->has('urls.update')
            ->has('urls.destroy')
            ->has('urls.index')
        );
});

test('the edit screen flags existing prices', function () {
    $currency = Currency::factory()->create(['default' => false]);
    // Variant creation triggers the HasUrls generator, which needs a default language.
    Language::factory()->create(['default' => true]);
    $variant = ProductVariant::factory()->create();

    Price::factory()->create([
        'currency_id' => $currency->id,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
    ]);

    $this->get(route('panel.settings.currencies.edit', $currency))
        ->assertInertia(fn (Assert $page) => $page->where('hasPrices', true));
});

test('a currency can be updated', function () {
    $currency = Currency::factory()->create([
        'code' => 'EUR',
        'name' => 'Euro',
        'exchange_rate' => 1.1,
        'decimal_places' => 2,
        'enabled' => true,
        'default' => false,
    ]);

    $this->put(route('panel.settings.currencies.update', $currency), [
        'code' => 'EUR',
        'name' => 'Euro (EU)',
        'exchange_rate' => '1.25',
        'decimal_places' => '3',
        'enabled' => false,
    ])->assertRedirect(route('panel.settings.currencies.index'))
        ->assertSessionHas('success');

    $currency->refresh();

    expect($currency->name)->toBe('Euro (EU)');
    expect((float) $currency->exchange_rate)->toBe(1.25);
    expect($currency->decimal_places)->toBe(3);
    expect($currency->enabled)->toBeFalse();
});

test('updating a currency to default un-defaults whichever currency was default', function () {
    $default = Currency::factory()->create(['code' => 'GBP', 'default' => true]);
    $currency = Currency::factory()->create(['code' => 'EUR', 'default' => false]);

    $this->put(route('panel.settings.currencies.update', $currency), [
        'code' => 'EUR',
        'name' => 'Euro',
        'exchange_rate' => '1.2',
        'decimal_places' => '2',
        'default' => true,
    ])->assertRedirect(route('panel.settings.currencies.index'));

    expect($default->fresh()->default)->toBeFalse();
    expect($currency->fresh()->default)->toBeTrue();
    expect(Currency::where('default', true)->count())->toBe(1);
});

test('unsetting default on the default currency is rejected with a flash error', function () {
    $currency = Currency::factory()->create(['code' => 'GBP', 'default' => true]);

    $this->from(route('panel.settings.currencies.edit', $currency))
        ->put(route('panel.settings.currencies.update', $currency), [
            'code' => 'GBP',
            'name' => 'Pound Sterling',
            'exchange_rate' => '1',
            'decimal_places' => '2',
            'enabled' => true,
            'default' => false,
        ])->assertRedirect(route('panel.settings.currencies.edit', $currency))
        ->assertSessionHas('error', __('panel::currencies.default_unset_blocked'));

    expect($currency->fresh()->default)->toBeTrue();
});

test('disabling the default currency is rejected with a flash error', function () {
    $currency = Currency::factory()->create(['code' => 'GBP', 'default' => true, 'enabled' => true]);

    $this->from(route('panel.settings.currencies.edit', $currency))
        ->put(route('panel.settings.currencies.update', $currency), [
            'code' => 'GBP',
            'name' => 'Pound Sterling',
            'exchange_rate' => '1',
            'decimal_places' => '2',
            'enabled' => false,
            'default' => true,
        ])->assertRedirect(route('panel.settings.currencies.edit', $currency))
        ->assertSessionHas('error', __('panel::currencies.default_disable_blocked'));

    expect($currency->fresh()->enabled)->toBeTrue();
});

test('disabling the default currency without submitting the default flag shows the disable message', function () {
    $currency = Currency::factory()->create(['code' => 'GBP', 'default' => true, 'enabled' => true]);

    $this->from(route('panel.settings.currencies.edit', $currency))
        ->put(route('panel.settings.currencies.update', $currency), [
            'code' => 'GBP',
            'name' => 'Pound Sterling',
            'exchange_rate' => '1',
            'decimal_places' => '2',
            'enabled' => false,
        ])->assertRedirect(route('panel.settings.currencies.edit', $currency))
        ->assertSessionHas('error', __('panel::currencies.default_disable_blocked'));

    expect($currency->fresh()->enabled)->toBeTrue();
});

test('the default currency cannot be deleted and shows a flash error', function () {
    $currency = Currency::factory()->create(['default' => true]);

    $this->from(route('panel.settings.currencies.edit', $currency))
        ->delete(route('panel.settings.currencies.destroy', $currency))
        ->assertRedirect(route('panel.settings.currencies.edit', $currency))
        ->assertSessionHas('error', __('panel::currencies.delete_blocked_default'));

    expect(Currency::find($currency->id))->not->toBeNull();
});

test('a currency with no prices can be deleted', function () {
    $currency = Currency::factory()->create(['default' => false]);

    $this->delete(route('panel.settings.currencies.destroy', $currency))
        ->assertRedirect(route('panel.settings.currencies.index'))
        ->assertSessionHas('success');

    expect(Currency::find($currency->id))->toBeNull();
});

test('a currency with prices cannot be deleted and shows a flash error', function () {
    $currency = Currency::factory()->create(['default' => false]);
    // Variant creation triggers the HasUrls generator, which needs a default language.
    Language::factory()->create(['default' => true]);
    $variant = ProductVariant::factory()->create();

    Price::factory()->create([
        'currency_id' => $currency->id,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
    ]);

    $this->from(route('panel.settings.currencies.edit', $currency))
        ->delete(route('panel.settings.currencies.destroy', $currency))
        ->assertRedirect(route('panel.settings.currencies.edit', $currency))
        ->assertSessionHas('error', __('panel::currencies.delete_blocked'));

    expect(Currency::find($currency->id))->not->toBeNull();
});
