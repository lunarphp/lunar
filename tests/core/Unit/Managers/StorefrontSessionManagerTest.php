<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Lunar\Base\StorefrontSessionInterface;
use Lunar\Exceptions\CustomerNotBelongsToUserException;
use Lunar\Managers\StorefrontSessionManager;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Lunar\Tests\Core\Stubs\User;
use Lunar\Tests\Core\TestCase;

use function Pest\Laravel\actingAs;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function (): void {
    setAuthUserConfig();

    Channel::factory()->create([
        'default' => true,
    ]);

    CustomerGroup::factory()->create([
        'default' => true,
    ]);

    Currency::factory()->create([
        'default' => true,
    ]);
});

test('can instantiate the manager', function (): void {
    /** @var StorefrontSessionManager */
    $manager = app(StorefrontSessionInterface::class);

    expect($manager)->toBeInstanceOf(StorefrontSessionManager::class);
});

test('can initialise the channel', function (): void {
    $defaultChannel = Channel::getDefault();

    /** @var StorefrontSessionManager */
    $manager = app(StorefrontSessionInterface::class);

    expect($manager->getChannel()->id)->toBe($defaultChannel->id);
});

test('can initialise the customer groups', function (): void {
    $defaultCustomerGroup = CustomerGroup::getDefault();

    /** @var StorefrontSessionManager */
    $manager = app(StorefrontSessionInterface::class);

    expect($manager->getCustomerGroups())
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(1);

    expect($manager->getCustomerGroups()->first()->id)->toBe($defaultCustomerGroup->id);
});

test('can initialise the currency', function (): void {
    $currency = Currency::getDefault();

    /** @var StorefrontSessionManager */
    $manager = app(StorefrontSessionInterface::class);

    expect($manager->getCurrency()->id)->toBe($currency->id);
});

test('can initialise the customer without authenticated user', function (): void {
    /** @var StorefrontSessionManager */
    $manager = app(StorefrontSessionInterface::class);

    expect($manager->getCustomer())->toBeNull();
});

test('can initialise the latest customer for the authenticated user', function (): void {
    /** @var User */
    $user = User::factory()->create();

    $customers = Customer::factory(5)->create();

    $user->customers()->sync($customers->pluck('id'));

    expect($user->customers()->get())->toHaveCount(5);

    actingAs($user);

    /** @var StorefrontSessionManager */
    $manager = app(StorefrontSessionInterface::class);

    expect($manager->getCustomer()->id)->toBe($customers->last()->id);
});

test('can set channel', function (): void {
    $defaultChannel = Channel::getDefault();

    /** @var Channel */
    $otherChannel = Channel::factory()->create([
        'default' => false,
    ]);

    /** @var StorefrontSessionManager */
    $manager = app(StorefrontSessionInterface::class);

    $sessionKey = $manager->getSessionKey().'_channel';

    expect($manager->getChannel()->id)->toBe($defaultChannel->id);
    expect(Session::get($sessionKey))->toBe($defaultChannel->handle);

    $manager->setChannel($otherChannel);

    expect($manager->getChannel()->id)->toBe($otherChannel->id);
    expect(Session::get($sessionKey))->toBe($otherChannel->handle);
});

test('can set multiple customer group', function (): void {
    $defaultCustomerGroup = CustomerGroup::getDefault();

    /** @var Collection<CustomerGroup> */
    $otherCustomerGroups = CustomerGroup::factory(4)->create([
        'default' => false,
    ]);

    /** @var StorefrontSessionManager */
    $manager = app(StorefrontSessionInterface::class);

    $sessionKey = $manager->getSessionKey().'_customer_groups';

    expect($manager->getCustomerGroups())->toHaveCount(1);
    expect($manager->getCustomerGroups()->first()->id)->toBe($defaultCustomerGroup->id);
    expect(Session::get($sessionKey))->toBe([$defaultCustomerGroup->handle]);

    $manager->setCustomerGroups($otherCustomerGroups);

    expect($manager->getCustomerGroups())->toHaveCount(4);
    expect($manager->getCustomerGroups()->first()->id)->toBe($otherCustomerGroups->first()->id);
    expect(Session::get($sessionKey))->toBe($otherCustomerGroups->pluck('handle')->toArray());
});

test('can set a single customer group', function (): void {
    $defaultCustomerGroup = CustomerGroup::getDefault();

    /** @var CustomerGroup */
    $otherCustomerGroup = CustomerGroup::factory()->create([
        'default' => false,
    ]);

    /** @var StorefrontSessionManager */
    $manager = app(StorefrontSessionInterface::class);

    $sessionKey = $manager->getSessionKey().'_customer_groups';

    expect($manager->getCustomerGroups())->toHaveCount(1);
    expect($manager->getCustomerGroups()->first()->id)->toBe($defaultCustomerGroup->id);
    expect(Session::get($sessionKey))->toBe([$defaultCustomerGroup->handle]);

    $manager->setCustomerGroup($otherCustomerGroup);

    expect($manager->getCustomerGroups())->toHaveCount(1);
    expect($manager->getCustomerGroups()->first()->id)->toBe($otherCustomerGroup->id);
    expect(Session::get($sessionKey))->toBe([$otherCustomerGroup->handle]);
});

test('can set currency', function (): void {
    $defaultCurrency = Currency::getDefault();

    /** @var Currency */
    $otherCurrency = Currency::factory()->create([
        'default' => false,
    ]);

    /** @var StorefrontSessionManager */
    $manager = app(StorefrontSessionInterface::class);

    $sessionKey = $manager->getSessionKey().'_currency';

    expect($manager->getCurrency()->id)->toBe($defaultCurrency->id);
    expect(Session::get($sessionKey))->toBe($defaultCurrency->code);

    $manager->setCurrency($otherCurrency);

    expect($manager->getCurrency()->id)->toBe($otherCurrency->id);
    expect(Session::get($sessionKey))->toBe($otherCurrency->code);
});

test('can set customer', function (): void {
    $user = User::factory()->create();

    $customers = Customer::factory(5)->create();

    $user->customers()->sync($customers->pluck('id'));

    /** @var StorefrontSessionManager */
    $manager = app(StorefrontSessionInterface::class);

    $sessionKey = $manager->getSessionKey().'_customer';

    /** @var Customer */
    $customer = $customers->first();

    expect($manager->getCustomer())->toBeNull();
    expect(Session::get($sessionKey))->toBeNull();

    $manager->setCustomer($customer);

    expect($manager->getCustomer()->id)->toBe($customer->id);
    expect(Session::get($sessionKey))->toBe($customer->id);
});

test('ensure customer belongs to user', function (): void {
    /** @var User */
    $user = User::factory()->create();

    $customers = Customer::factory(5)->create();

    actingAs($user);

    /** @var StorefrontSessionManager */
    $manager = app(StorefrontSessionInterface::class);

    /** @var Customer */
    $unrelatedCustomer = $customers->first();

    $manager->setCustomer($unrelatedCustomer);
})->throws(CustomerNotBelongsToUserException::class);

test('can forget all values', function (): void {
    /** @var User */
    $user = User::factory()->create();

    /** @var Customer */
    $customer = Customer::factory()->create();

    $user->customers()->sync($customer->id);

    actingAs($user);

    /** @var StorefrontSessionManager */
    $manager = app(StorefrontSessionInterface::class);

    $sessionKey = $manager->getSessionKey();

    expect(Session::has($sessionKey.'_channel'))->toBeTrue();
    expect(Session::has($sessionKey.'_customer_groups'))->toBeTrue();
    expect(Session::has($sessionKey.'_currency'))->toBeTrue();
    expect(Session::has($sessionKey.'_customer'))->toBeTrue();

    $manager->forget();

    expect(Session::has($sessionKey.'_channel'))->toBeFalse();
    expect(Session::has($sessionKey.'_customer_groups'))->toBeFalse();
    expect(Session::has($sessionKey.'_currency'))->toBeFalse();
    expect(Session::has($sessionKey.'_customer'))->toBeFalse();
});
