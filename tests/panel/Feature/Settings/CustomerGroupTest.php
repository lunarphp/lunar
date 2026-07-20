<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');
});

test('the customer groups index renders with the real group list', function () {
    CustomerGroup::factory()->create(['name' => 'Retail', 'handle' => 'retail', 'default' => true]);
    CustomerGroup::factory()->create(['name' => 'Trade', 'handle' => 'trade', 'default' => false]);

    $this->get(route('panel.settings.customer-groups.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/customer-groups/Index')
            ->has('customerGroups.data', 2)
            ->where('customerGroups.data.0.name', 'Retail')
            ->where('customerGroups.data.0.default', true)
            ->whereType('customerGroups.data.0.default', 'boolean')
            ->where('customerGroups.data.1.name', 'Trade')
            ->has('urls.store')
        );
});

test('customer groups carry first-party row actions, with delete omitted for the default group', function () {
    CustomerGroup::factory()->create(['name' => 'Retail', 'default' => true]);
    CustomerGroup::factory()->create(['name' => 'Trade', 'default' => false]);

    $this->get(route('panel.settings.customer-groups.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('tableActions', fn ($actions) => collect($actions)->pluck('key')->all() === ['edit', 'delete'])
            ->where('customerGroups.data.0._actions', fn ($actions) => isset($actions['edit']) && ! isset($actions['delete']))
            ->where('customerGroups.data.1._actions', fn ($actions) => isset($actions['edit'], $actions['delete']))
        );
});

test('a customer group can be created with an auto-generated handle', function () {
    $this->post(route('panel.settings.customer-groups.store'), [
        'name' => 'Trade Customers',
    ])->assertRedirect(route('panel.settings.customer-groups.index'))
        ->assertSessionHas('success');

    $customerGroup = CustomerGroup::where('name', 'Trade Customers')->first();

    expect($customerGroup)->not->toBeNull();
    expect($customerGroup->handle)->toBe('trade-customers');
    expect($customerGroup->default)->toBeFalse();
});

test('creating a second group as default un-defaults the first', function () {
    $first = CustomerGroup::factory()->create(['name' => 'Retail', 'default' => true]);

    $this->post(route('panel.settings.customer-groups.store'), [
        'name' => 'Trade',
        'default' => true,
    ])->assertRedirect(route('panel.settings.customer-groups.index'));

    expect($first->fresh()->default)->toBeFalse();
    expect(CustomerGroup::where('name', 'Trade')->first()->default)->toBeTrue();
    expect(CustomerGroup::where('default', true)->count())->toBe(1);
});

test('handle must be unique', function () {
    CustomerGroup::factory()->create(['handle' => 'trade']);

    $this->post(route('panel.settings.customer-groups.store'), [
        'name' => 'Trade 2',
        'handle' => 'trade',
    ])->assertSessionHasErrors('handle');
});

test('a colliding auto-generated handle is rejected as a validation error', function () {
    CustomerGroup::factory()->create(['name' => 'Trade', 'handle' => 'trade']);

    $this->post(route('panel.settings.customer-groups.store'), [
        'name' => 'Trade',
    ])->assertSessionHasErrors('handle');

    expect(CustomerGroup::count())->toBe(1);
});

test('handle uniqueness is checked against the slugged form', function () {
    CustomerGroup::factory()->create(['name' => 'Trade', 'handle' => 'trade-customers']);

    $this->post(route('panel.settings.customer-groups.store'), [
        'name' => 'Another Group',
        'handle' => 'Trade Customers',
    ])->assertSessionHasErrors('handle');

    expect(CustomerGroup::count())->toBe(1);
});

test('the customer group edit screen renders with the group data', function () {
    $customerGroup = CustomerGroup::factory()->create(['name' => 'Trade', 'handle' => 'trade', 'default' => false]);

    $this->get(route('panel.settings.customer-groups.edit', $customerGroup))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/customer-groups/Edit')
            ->where('customerGroup.id', $customerGroup->id)
            ->where('customerGroup.name', 'Trade')
            ->where('customerGroup.handle', 'trade')
            ->where('customerGroup.default', false)
            ->where('hasCustomers', false)
            ->has('urls.update')
            ->has('urls.destroy')
        );
});

test('a customer group can be updated', function () {
    $customerGroup = CustomerGroup::factory()->create(['name' => 'Trade', 'handle' => 'trade', 'default' => false]);

    $this->put(route('panel.settings.customer-groups.update', $customerGroup), [
        'name' => 'Wholesale',
        'handle' => 'wholesale',
    ])->assertRedirect(route('panel.settings.customer-groups.index'))
        ->assertSessionHas('success');

    $customerGroup->refresh();

    expect($customerGroup->name)->toBe('Wholesale');
    expect($customerGroup->handle)->toBe('wholesale');
});

test('updating a group to default un-defaults whichever group was default', function () {
    $default = CustomerGroup::factory()->create(['name' => 'Retail', 'default' => true]);
    $customerGroup = CustomerGroup::factory()->create(['name' => 'Trade', 'default' => false]);

    $this->put(route('panel.settings.customer-groups.update', $customerGroup), [
        'name' => 'Trade',
        'handle' => $customerGroup->handle,
        'default' => true,
    ])->assertRedirect(route('panel.settings.customer-groups.index'));

    expect($default->fresh()->default)->toBeFalse();
    expect($customerGroup->fresh()->default)->toBeTrue();
    expect(CustomerGroup::where('default', true)->count())->toBe(1);
});

test('unsetting default on the default group is rejected with a flash error', function () {
    $customerGroup = CustomerGroup::factory()->create(['name' => 'Retail', 'default' => true]);

    $this->from(route('panel.settings.customer-groups.edit', $customerGroup))
        ->put(route('panel.settings.customer-groups.update', $customerGroup), [
            'name' => 'Retail',
            'handle' => $customerGroup->handle,
            'default' => false,
        ])->assertRedirect(route('panel.settings.customer-groups.edit', $customerGroup))
        ->assertSessionHas('error', __('panel::customer_groups.default_unset_blocked'));

    expect($customerGroup->fresh()->default)->toBeTrue();
});

test('the default group cannot be deleted and shows a flash error', function () {
    $customerGroup = CustomerGroup::factory()->create(['default' => true]);

    $this->from(route('panel.settings.customer-groups.edit', $customerGroup))
        ->delete(route('panel.settings.customer-groups.destroy', $customerGroup))
        ->assertRedirect(route('panel.settings.customer-groups.edit', $customerGroup))
        ->assertSessionHas('error', __('panel::customer_groups.delete_blocked_default'));

    expect(CustomerGroup::find($customerGroup->id))->not->toBeNull();
});

test('a group with no customers can be deleted', function () {
    $customerGroup = CustomerGroup::factory()->create(['default' => false]);

    $this->delete(route('panel.settings.customer-groups.destroy', $customerGroup))
        ->assertRedirect(route('panel.settings.customer-groups.index'))
        ->assertSessionHas('success');

    expect(CustomerGroup::find($customerGroup->id))->toBeNull();
});

test('a group with customers cannot be deleted and shows a flash error', function () {
    $customerGroup = CustomerGroup::factory()->create(['default' => false]);
    $customerGroup->customers()->attach(Customer::factory()->create());

    $this->from(route('panel.settings.customer-groups.edit', $customerGroup))
        ->delete(route('panel.settings.customer-groups.destroy', $customerGroup))
        ->assertRedirect(route('panel.settings.customer-groups.edit', $customerGroup))
        ->assertSessionHas('error', __('panel::customer_groups.delete_blocked'));

    expect(CustomerGroup::find($customerGroup->id))->not->toBeNull();
});
