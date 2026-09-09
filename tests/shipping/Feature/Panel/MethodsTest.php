<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Staff;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Tests\Shipping\PanelTestCase;

uses(PanelTestCase::class)->group('shipping', 'shipping-panel');

beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');
    $this->gbp = Currency::factory()->create(['code' => 'GBP', 'decimal_places' => 2, 'default' => true]);
});

test('the methods index lists methods with their driver label and availability', function () {
    [$trade, $retail] = CustomerGroup::factory()->count(2)->create();
    $method = ShippingMethod::factory()->create(['name' => 'Standard', 'code' => 'STD', 'driver' => 'ship-by']);
    $method->customerGroups()->sync([$trade->id => ['enabled' => true, 'visible' => true], $retail->id => ['enabled' => false, 'visible' => true]]);

    $this->get(route('panel.settings.shipping.methods.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('shipping::settings/shipping/methods/Index', false)
            ->has('methods.data', 1)
            ->where('methods.data.0.code', 'STD')
            ->where('methods.data.0.driver_label', 'Standard')
            ->where('methods.data.0.enabled_groups_count', 1)
            ->where('methods.data.0.groups_count', 2)
            ->where('drivers', fn ($drivers) => collect($drivers)->pluck('key')->contains('ship-by') && collect($drivers)->pluck('key')->contains('free-shipping'))
            ->where('tableActions', fn ($actions) => collect($actions)->pluck('key')->all() === ['edit', 'delete'])
        );
});

test('a new method takes its charge basis and is offered to every customer group', function () {
    CustomerGroup::factory()->count(2)->create();

    $this->post(route('panel.settings.shipping.methods.store'), [
        'name' => 'Heavy goods',
        'code' => 'HEAVY',
        'driver' => 'ship-by',
        'data' => ['charge_by' => 'weight'],
    ])->assertRedirect()->assertSessionHas('success');

    $method = ShippingMethod::where('code', 'HEAVY')->first();

    expect($method->data['charge_by'])->toBe('weight')
        ->and($method->customerGroups()->wherePivot('enabled', true)->count())->toBe(2);
});

test('codes are unique and drivers come from the registry', function () {
    ShippingMethod::factory()->create(['code' => 'STD']);

    $this->post(route('panel.settings.shipping.methods.store'), ['name' => 'Dup', 'code' => 'STD', 'driver' => 'teleport'])
        ->assertSessionHasErrors(['code', 'driver']);
});

test('the edit screen decodes driver data and the customer group pivot', function () {
    $group = CustomerGroup::factory()->create(['name' => 'Trade']);
    $method = ShippingMethod::factory()->create([
        'driver' => 'free-shipping',
        'data' => ['minimum_spend' => ['GBP' => 5000], 'use_discount_amount' => true],
        'weight_unit' => 'kg',
        'min_weight' => 1,
        'max_weight' => 30,
    ]);
    $method->customerGroups()->sync([$group->id => ['enabled' => true, 'visible' => false, 'starts_at' => '2026-01-01 00:00:00']]);

    $this->get(route('panel.settings.shipping.methods.edit', $method))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('shipping::settings/shipping/methods/Edit', false)
            ->where('method.driver', 'free-shipping')
            ->where('method.data.minimum_spend.GBP', fn ($value) => (float) $value === 50.0)
            ->where('method.data.use_discount_amount', true)
            ->where('method.data.schedule', null)
            ->where('method.weight_unit', 'kg')
            ->where('weightUnits', fn ($units) => collect($units)->contains('kg'))
            ->where('customerGroups.0.name', 'Trade')
            ->where('customerGroups.0.enabled', true)
            ->where('customerGroups.0.visible', false)
            ->where('customerGroups.0.starts_at', '2026-01-01')
        );
});

test('updating a method scales the minimum spend, stores the schedule and syncs groups', function () {
    $group = CustomerGroup::factory()->create();
    $method = ShippingMethod::factory()->create(['driver' => 'free-shipping', 'data' => ['private' => 'kept']]);

    $schedule = collect(range(1, 7))->mapWithKeys(fn ($day) => [(string) $day => ['enabled' => $day <= 5, 'from' => '09:00', 'to' => '17:00']])->all();

    $this->put(route('panel.settings.shipping.methods.update', $method), [
        'name' => 'Free delivery',
        'code' => $method->code,
        'driver' => 'free-shipping',
        'stock_available' => true,
        'weight_unit' => 'kg',
        'min_weight' => 0,
        'max_weight' => 20,
        'data' => [
            'minimum_spend' => ['GBP' => '75.00'],
            'use_discount_amount' => false,
            'schedule' => $schedule,
        ],
        'customer_groups' => [
            ['id' => $group->id, 'enabled' => true, 'visible' => true, 'starts_at' => null, 'ends_at' => null],
        ],
    ])->assertRedirect()->assertSessionHas('success');

    $method->refresh();
    $data = (array) $method->data;

    expect($method->name)->toBe('Free delivery')
        ->and($method->stock_available)->toBeTruthy()
        ->and($method->max_weight)->toEqual(20)
        ->and($data['minimum_spend'])->toBe(['GBP' => 7500])
        ->and($data['private'])->toBe('kept')
        ->and($data['schedule']['1']['enabled'])->toBeTrue()
        ->and($data['schedule']['6']['enabled'])->toBeFalse()
        ->and($data['schedule']['6']['from'])->toBeNull()
        ->and($method->customerGroups()->count())->toBe(1);
});

test('a schedule can be removed and weight limits need a sensible range', function () {
    $method = ShippingMethod::factory()->create(['data' => ['schedule' => ['1' => ['enabled' => true]]]]);

    $this->put(route('panel.settings.shipping.methods.update', $method), [
        'name' => $method->name,
        'code' => $method->code,
        'driver' => 'ship-by',
        'weight_unit' => 'kg',
        'min_weight' => 10,
        'max_weight' => 5,
        'data' => ['schedule' => null],
    ])->assertSessionHasErrors('max_weight');

    $this->put(route('panel.settings.shipping.methods.update', $method), [
        'name' => $method->name,
        'code' => $method->code,
        'driver' => 'ship-by',
        'data' => ['schedule' => null],
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect((array) $method->refresh()->data)->not->toHaveKey('schedule');
});

test('a method can be deleted', function () {
    $method = ShippingMethod::factory()->create();

    $this->delete(route('panel.settings.shipping.methods.destroy', $method))
        ->assertRedirect(route('panel.settings.shipping.methods.index'));

    expect(ShippingMethod::find($method->id))->toBeNull();
});
