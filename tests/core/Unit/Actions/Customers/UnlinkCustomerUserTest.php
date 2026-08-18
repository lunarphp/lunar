<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Customers\UnlinkCustomerUser;
use Lunar\Core\Models\Customer;
use Lunar\Tests\Core\Stubs\User;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('unlinks the user from the customer', function () {
    $customer = Customer::factory()->create();
    $user = User::factory()->create();
    $customer->users()->attach($user);

    app(UnlinkCustomerUser::class)->execute($customer, $user->id);

    $this->assertDatabaseMissing('lunar_customer_user', [
        'customer_id' => $customer->id,
        'user_id' => $user->id,
    ]);
});

test('leaves other links untouched', function () {
    $customer = Customer::factory()->create();
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $customer->users()->attach([$user->id, $otherUser->id]);

    app(UnlinkCustomerUser::class)->execute($customer, $user->id);

    $this->assertDatabaseHas('lunar_customer_user', [
        'customer_id' => $customer->id,
        'user_id' => $otherUser->id,
    ]);
});
