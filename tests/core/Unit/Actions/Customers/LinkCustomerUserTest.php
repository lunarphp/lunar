<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Customers\LinkCustomerUser;
use Lunar\Core\Models\Customer;
use Lunar\Tests\Core\Stubs\User;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('links a user found by email to the customer', function () {
    $customer = Customer::factory()->create();
    $user = User::factory()->create(['email' => 'tony@stark.com']);

    app(LinkCustomerUser::class)->execute($customer, 'tony@stark.com');

    $this->assertDatabaseHas('lunar_customer_user', [
        'customer_id' => $customer->id,
        'user_id' => $user->id,
    ]);
});

test('does not detach existing links when linking another user', function () {
    $customer = Customer::factory()->create();
    $existingUser = User::factory()->create();
    $customer->users()->attach($existingUser);

    $newUser = User::factory()->create(['email' => 'peter@parker.com']);

    app(LinkCustomerUser::class)->execute($customer, 'peter@parker.com');

    $this->assertDatabaseHas('lunar_customer_user', [
        'customer_id' => $customer->id,
        'user_id' => $existingUser->id,
    ]);
    $this->assertDatabaseHas('lunar_customer_user', [
        'customer_id' => $customer->id,
        'user_id' => $newUser->id,
    ]);
});

test('throws when no user matches the email', function () {
    $customer = Customer::factory()->create();

    app(LinkCustomerUser::class)->execute($customer, 'nobody@nowhere.com');
})->throws(ModelNotFoundException::class);
