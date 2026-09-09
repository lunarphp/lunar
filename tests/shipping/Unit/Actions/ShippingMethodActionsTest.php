<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Shipping\Contracts\Actions\ShippingMethods\CreatesShippingMethod;
use Lunar\Shipping\Contracts\Actions\ShippingMethods\DeletesShippingMethod;
use Lunar\Shipping\Contracts\Actions\ShippingMethods\UpdatesShippingMethod;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Tests\Shipping\TestCase;

uses(TestCase::class, RefreshDatabase::class)->group('shipping', 'shipping-actions');

test('a new method is available to every customer group from now', function () {
    CustomerGroup::factory()->count(2)->create();

    $method = app(CreatesShippingMethod::class)->execute([
        'name' => 'Standard',
        'code' => 'STD',
        'driver' => 'ship-by',
        'data' => ['charge_by' => 'weight'],
    ]);

    expect($method->customerGroups()->count())->toBe(2)
        ->and($method->customerGroups()->first()->pivot->enabled)->toBeTruthy()
        ->and($method->customerGroups()->first()->pivot->visible)->toBeTruthy()
        ->and($method->data['charge_by'])->toBe('weight');
});

test('driver data merges key by key and a null removes the key', function () {
    $method = ShippingMethod::factory()->create([
        'data' => ['charge_by' => 'cart_total', 'private_key' => 'kept', 'schedule' => ['1' => ['enabled' => true]]],
    ]);

    app(UpdatesShippingMethod::class)->execute($method, [
        'name' => 'Renamed',
        'data' => ['charge_by' => 'weight', 'schedule' => null],
    ]);

    $data = (array) $method->refresh()->data;

    expect($method->name)->toBe('Renamed')
        ->and($data)->toBe(['charge_by' => 'weight', 'private_key' => 'kept']);
});

test('customer group availability rows replace the pivot', function () {
    [$trade, $retail] = CustomerGroup::factory()->count(2)->create();
    $method = ShippingMethod::factory()->create();
    $method->customerGroups()->sync([$trade->id => ['enabled' => true, 'visible' => true]]);

    app(UpdatesShippingMethod::class)->execute($method, [
        'customer_groups' => [
            ['id' => $retail->id, 'enabled' => true, 'visible' => false, 'starts_at' => '2026-01-01 00:00:00', 'ends_at' => null],
        ],
    ]);

    $rows = $method->customerGroups()->get();

    expect($rows)->toHaveCount(1)
        ->and($rows->first()->id)->toBe($retail->id)
        ->and($rows->first()->pivot->enabled)->toBeTruthy()
        ->and($rows->first()->pivot->visible)->toBeFalsy()
        ->and($rows->first()->pivot->starts_at)->toStartWith('2026-01-01');
});

test('deleting a method detaches its groups', function () {
    $method = ShippingMethod::factory()->create();
    $method->customerGroups()->sync([CustomerGroup::factory()->create()->id]);

    app(DeletesShippingMethod::class)->execute($method);

    expect(ShippingMethod::find($method->id))->toBeNull();
});
