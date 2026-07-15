<?php

use Lunar\Core\Models\Customer;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

it('denies customer routes to staff without the manage-customers permission', function () {
    $staff = Staff::factory()->create(['admin' => false]);
    $customer = Customer::factory()->create();

    $this->actingAs($staff, 'staff');

    $this->get('/panel/customers')->assertForbidden();
    $this->get("/panel/customers/{$customer->id}/edit")->assertForbidden();
    $this->put("/panel/customers/{$customer->id}", [])->assertForbidden();
    $this->delete("/panel/customers/{$customer->id}")->assertForbidden();
});

it('allows customer routes to staff granted the manage-customers permission', function () {
    $staff = Staff::factory()->create(['admin' => false]);
    $staff->givePermissionTo('sales:manage-customers');

    $this->actingAs($staff, 'staff')
        ->get('/panel/customers')
        ->assertOk();
});

it('allows customer routes to admin staff without an explicit permission', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    $this->actingAs($staff, 'staff')
        ->get('/panel/customers')
        ->assertOk();
});

it('denies channel routes to staff without the settings permission', function () {
    $staff = Staff::factory()->create(['admin' => false]);

    $this->actingAs($staff, 'staff');

    $this->get('/panel/settings/channels')->assertForbidden();
    $this->post('/panel/settings/channels', [])->assertForbidden();
});

it('allows channel routes to staff granted the settings permission', function () {
    $staff = Staff::factory()->create(['admin' => false]);
    $staff->givePermissionTo('settings:core');

    $this->actingAs($staff, 'staff')
        ->get('/panel/settings/channels')
        ->assertOk();
});

it('does not let the customers permission unlock channel routes', function () {
    $staff = Staff::factory()->create(['admin' => false]);
    $staff->givePermissionTo('sales:manage-customers');

    $this->actingAs($staff, 'staff')
        ->get('/panel/settings/channels')
        ->assertForbidden();
});

it('hides the customers navigation item from staff without the permission', function () {
    $staff = Staff::factory()->create(['admin' => false]);

    $this->actingAs($staff, 'staff')
        ->get('/panel')
        ->assertInertia(fn ($page) => $page
            ->where('navigation.groups', fn ($groups) => ! collect($groups)->pluck('key')->contains('sales')));
});

it('shows the customers navigation item to staff with the permission', function () {
    $staff = Staff::factory()->create(['admin' => false]);
    $staff->givePermissionTo('sales:manage-customers');

    $this->actingAs($staff, 'staff')
        ->get('/panel')
        ->assertInertia(fn ($page) => $page
            ->where('navigation.groups', fn ($groups) => collect($groups)->pluck('key')->contains('sales')));
});
