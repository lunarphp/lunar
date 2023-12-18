<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Illuminate\Support\Facades\Session;
use Lunar\Base\StorefrontSessionInterface;
use Lunar\Exceptions\CustomerNotBelongsToUserException;
use Lunar\Managers\StorefrontSessionManager;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Stubs\User as StubUser;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

//function setAuthUserConfig()
//{
//    Config::set('auth.providers.users.model', 'Lunar\Tests\Stubs\User');
//}

test('can instantiate manager', function () {
    Channel::factory()->create([
        'default' => true,
    ]);

    CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $manager = app(StorefrontSessionInterface::class);
    expect($manager)->toBeInstanceOf(StorefrontSessionManager::class);
});

test('can initialise customer groups', function () {
    Channel::factory()->create([
        'default' => true,
    ]);

    CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $manager = app(StorefrontSessionInterface::class);

    expect($manager->getCustomerGroups())->toHaveCount(1);
});

test('can initialise the channel', function () {
    $channel = Channel::factory()->create([
        'default' => true,
    ]);

    $manager = app(StorefrontSessionInterface::class);

    expect($manager->getChannel()->id)->toEqual($channel->id);
});

test('can initialise the currency', function () {
    Channel::factory()->create([
        'default' => true,
    ]);

    $currency = Currency::factory()->create();

    $manager = app(StorefrontSessionInterface::class);

    expect($manager->getCurrency()->id)->toEqual($currency->id);
});

test('can initialise the customer', function () {
    Channel::factory()->create([
        'default' => true,
    ]);

    setAuthUserConfig();

    $user = StubUser::factory()->create();

    $customers = Customer::factory(5)->create();

    $user->customers()->sync($customers->pluck('id'));

    expect($user->customers()->get())->toHaveCount(5);

    $this->assertDatabaseCount((new Customer)->getTable(), 5);

    $manager = app(StorefrontSessionInterface::class);

    expect($manager->getCustomer())->toBeNull();

    $this->actingAs($user);

    expect($manager->getCustomer()->id)->toEqual($customers->last()->id);
});

test('can set channel', function () {
    Channel::factory()->create([
        'default' => true,
    ]);

    $channelB = Channel::factory()->create([
        'default' => false,
    ]);

    $manager = app(StorefrontSessionInterface::class);

    $manager->setChannel($channelB);

    expect($manager->getChannel()->id)->toEqual($channelB->id);
});

test('can set currency', function () {
    Channel::factory()->create([
        'default' => true,
    ]);

    Currency::factory()->create([
        'default' => true,
    ]);

    $currencyB = Currency::factory()->create([
        'default' => true,
    ]);

    $manager = app(StorefrontSessionInterface::class);

    $manager->setCurrency($currencyB);

    expect($manager->getCurrency()->id)->toEqual($currencyB->id);
});

test('can set customer groups', function () {
    Channel::factory()->create([
        'default' => true,
    ]);

    CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $groupB = CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $manager = app(StorefrontSessionInterface::class);

    $manager->setCustomerGroup($groupB);

    expect($manager->getCustomerGroups()->first()->id)->toEqual($groupB->id);

    expect(Session::get(
        $manager->getSessionKey().'_customer_groups'
    ))->toEqual([$groupB->handle]);

    expect($manager->getCustomerGroups())->toHaveCount(1);
});

test('can set customer', function () {
    Channel::factory()->create([
        'default' => true,
    ]);

    setAuthUserConfig();

    $user = StubUser::factory()->create();

    // $this->actingAs($user);
    $customers = Customer::factory(5)->create();

    $user->customers()->sync($customers->pluck('id'));

    $manager = app(StorefrontSessionInterface::class);

    $customer = $customers->first();

    $manager->setCustomer($customer);

    expect($manager->getCustomer()->id)->toEqual($customer->id);
});

test('ensure customer belongs to user', function () {
    Channel::factory()->create([
        'default' => true,
    ]);

    setAuthUserConfig();

    $user = StubUser::factory()->create();

    $this->actingAs($user);

    $customers = Customer::factory(5)->create();

    $manager = app(StorefrontSessionInterface::class);

    $customer = $customers->first();

    $this->expectException(CustomerNotBelongsToUserException::class);

    $manager->setCustomer($customer);
});
