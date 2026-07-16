<?php

use Lunar\Core\Models\Customer;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Core\Stubs\User;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

it('links a user to a customer by email', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();
    $user = User::factory()->create(['email' => 'ada@example.com']);

    $this->post(route('panel.customers.users.store', $customer), [
        'email' => 'ada@example.com',
    ])->assertRedirect()
        ->assertSessionHas('success', 'User linked.');

    expect($customer->users()->pluck('users.id')->all())->toBe([$user->id]);
});

it('returns a validation error when no user matches the given email', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();

    $this->post(route('panel.customers.users.store', $customer), [
        'email' => 'missing@example.com',
    ])->assertSessionHasErrors(['email']);

    expect($customer->users()->count())->toBe(0);
});

it('validates the email field is present and well formed', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();

    $this->post(route('panel.customers.users.store', $customer), [
        'email' => 'not-an-email',
    ])->assertSessionHasErrors(['email']);
});

it('unlinks a user from a customer', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();
    $user = User::factory()->create();
    $customer->users()->attach($user);

    $this->delete(route('panel.customers.users.destroy', ['customer' => $customer, 'user' => $user->id]))
        ->assertRedirect()
        ->assertSessionHas('success', 'User unlinked.');

    expect($customer->users()->count())->toBe(0);
});

it('records user link changes on the customer activity log', function () {
    // The panel TestCase disables activity logging globally; this test is about the log.
    activity()->enableLogging();

    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();
    $user = User::factory()->create(['email' => 'ada@example.com']);

    $this->post(route('panel.customers.users.store', $customer), ['email' => 'ada@example.com']);
    $this->delete(route('panel.customers.users.destroy', ['customer' => $customer, 'user' => $user->id]));

    $descriptions = $customer->activities()->pluck('description');

    expect($descriptions)->toContain('user-linked')
        ->toContain('user-unlinked');
});
