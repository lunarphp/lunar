<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');
});

test('the currencies index renders with the real currency list', function () {
    Currency::factory()->create(['code' => 'GBP', 'name' => 'Pound Sterling', 'default' => true]);
    Currency::factory()->create(['code' => 'EUR', 'name' => 'Euro', 'default' => false]);

    $this->get(route('panel.settings.currencies.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/currencies/Index')
            ->has('currencies.data', 2)
            ->where('currencies.data.0.code', 'EUR')
            ->where('currencies.data.1.code', 'GBP')
            ->where('currencies.data.1.default', true)
            ->where('currencies.data.0.default', false)
            ->has('urls.store')
        );
});

test('currencies carry first-party row actions, with delete omitted for the default currency', function () {
    // Order by code: EUR (non-default) then GBP (default).
    Currency::factory()->create(['code' => 'GBP', 'default' => true]);
    Currency::factory()->create(['code' => 'EUR', 'default' => false]);

    $this->get(route('panel.settings.currencies.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('tableActions', fn ($actions) => collect($actions)->pluck('key')->all() === ['edit', 'delete'])
            ->where('currencies.data.0._actions', fn ($actions) => isset($actions['edit'], $actions['delete']))
            ->where('currencies.data.1._actions', fn ($actions) => isset($actions['edit']) && ! isset($actions['delete']))
        );
});

test('the flags are serialized as real booleans', function () {
    Currency::factory()->create(['code' => 'GBP', 'default' => true, 'enabled' => true]);

    $this->get(route('panel.settings.currencies.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->whereType('currencies.data.0.default', 'boolean')
            ->whereType('currencies.data.0.enabled', 'boolean')
            ->whereType('currencies.data.0.exchange_rate', ['double', 'integer'])
        );
});
