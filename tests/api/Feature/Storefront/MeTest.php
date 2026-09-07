<?php

use Lunar\Core\Models\Customer;
use Lunar\Tests\Api\Fixtures\CustomerGuardTestCase;
use Lunar\Tests\Core\Stubs\User;

uses(CustomerGuardTestCase::class);

beforeEach(function (): void {
    $this->store = $this->setUpStore();
});

test('guests are rejected', function (): void {
    $this->getJson('/api/storefront/v1/me')
        ->assertUnauthorized()
        ->assertJsonPath('errors.0.code', 'unauthenticated');
});

test('the authenticated user sees their latest customer', function (): void {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['first_name' => 'Ada', 'last_name' => 'Lovelace']);
    $customer->users()->attach($user);

    $this->actingAs($user, 'web')
        ->getJson('/api/storefront/v1/me')
        ->assertOk()
        ->assertJsonPath('data.id', $customer->public_id)
        ->assertJsonPath('data.type', 'customers')
        ->assertJsonPath('data.first_name', 'Ada');
});

test('a user without a customer record gets a 404 error object', function (): void {
    $this->actingAs(User::factory()->create(), 'web')
        ->getJson('/api/storefront/v1/me')
        ->assertNotFound()
        ->assertJsonPath('errors.0.code', 'customer_not_found');
});
