<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Lunar\Core\Contracts\StorefrontSession;
use Lunar\Core\DataObjects\StorefrontContext;
use Lunar\Core\Exceptions\CustomerNotBelongsToUserException;
use Lunar\Core\Managers\StorefrontSessionManager;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Region;
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

    // Pin the code: the factory draws a random currencyCode from a ~150-value
    // pool, which can collide with the explicit 'EUR' currencies other tests
    // create, violating the unique constraint on lunar_currencies.code.
    Currency::factory()->create([
        'default' => true,
        'code' => 'USD',
    ]);
});

test('can instantiate the manager', function (): void {
    /** @var StorefrontSessionManager */
    $manager = app(StorefrontSession::class);

    expect($manager)->toBeInstanceOf(StorefrontSessionManager::class);
});

test('can initialise the channel', function (): void {
    $defaultChannel = Channel::getDefault();

    /** @var StorefrontSessionManager */
    $manager = app(StorefrontSession::class);

    expect($manager->getChannel()->id)->toBe($defaultChannel->id);
});

test('can initialise the customer groups', function (): void {
    $defaultCustomerGroup = CustomerGroup::getDefault();

    /** @var StorefrontSessionManager */
    $manager = app(StorefrontSession::class);

    expect($manager->getCustomerGroups())
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(1);

    expect($manager->getCustomerGroups()->first()->id)->toBe($defaultCustomerGroup->id);
});

test('can initialise the currency', function (): void {
    $currency = Currency::getDefault();

    /** @var StorefrontSessionManager */
    $manager = app(StorefrontSession::class);

    expect($manager->getCurrency()->id)->toBe($currency->id);
});

test('can initialise the customer without authenticated user', function (): void {
    /** @var StorefrontSessionManager */
    $manager = app(StorefrontSession::class);

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
    $manager = app(StorefrontSession::class);

    expect($manager->getCustomer()->id)->toBe($customers->last()->id);
});

test('can set channel', function (): void {
    $defaultChannel = Channel::getDefault();

    /** @var Channel */
    $otherChannel = Channel::factory()->create([
        'default' => false,
    ]);

    /** @var StorefrontSessionManager */
    $manager = app(StorefrontSession::class);

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
    $manager = app(StorefrontSession::class);

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
    $manager = app(StorefrontSession::class);

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
        'code' => 'GBP',
    ]);

    /** @var StorefrontSessionManager */
    $manager = app(StorefrontSession::class);

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
    $manager = app(StorefrontSession::class);

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
    $manager = app(StorefrontSession::class);

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
    $manager = app(StorefrontSession::class);

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

test('context produces the resolved session selections', function (): void {
    /** @var StorefrontSessionManager */
    $manager = app(StorefrontSession::class);

    $context = $manager->context();

    expect($context)->toBeInstanceOf(StorefrontContext::class);
    expect($context->channel->id)->toBe(Channel::getDefault()->id);
    expect($context->currency->id)->toBe(Currency::getDefault()->id);
    expect($context->customer)->toBeNull();
    expect($context->customerGroups->pluck('id')->all())->toBe([CustomerGroup::getDefault()->id]);
    // no default language is seeded in beforeEach, so it falls back to null
    expect($context->language)->toBeNull();
});

test('context honours an explicitly set customer group rather than re-deriving', function (): void {
    /** @var CustomerGroup */
    $trade = CustomerGroup::factory()->create(['default' => false]);

    /** @var StorefrontSessionManager */
    $manager = app(StorefrontSession::class);
    $manager->setCustomerGroup($trade);

    expect($manager->context()->customerGroups->pluck('id')->all())->toBe([$trade->id]);
});

test('context carries the default language when one is configured', function (): void {
    $language = Language::factory()->create(['default' => true]);

    /** @var StorefrontSessionManager */
    $manager = app(StorefrontSession::class);

    expect($manager->context()->language->id)->toBe($language->id);
});

test('the session resolves the default region and carries it on the context', function (): void {
    $region = Region::factory()->create([
        'default' => true,
        'channel_id' => Channel::getDefault()->id,
        'currency_id' => Currency::getDefault()->id,
        'language_id' => Language::factory()->create(['default' => true])->id,
    ]);

    /** @var StorefrontSessionManager */
    $manager = app(StorefrontSession::class);

    expect($manager->getRegion()->id)->toBe($region->id);
    expect($manager->context()->region->id)->toBe($region->id);
});

test('can set the region', function (): void {
    $region = Region::factory()->create([
        'default' => false,
        'channel_id' => Channel::getDefault()->id,
        'currency_id' => Currency::getDefault()->id,
        'language_id' => Language::factory()->create(['default' => true])->id,
    ]);

    /** @var StorefrontSessionManager */
    $manager = app(StorefrontSession::class);
    $manager->setRegion($region);

    expect($manager->getRegion()->id)->toBe($region->id);
    expect(Session::get($manager->getSessionKey().'_region'))->toBe($region->handle);
});

test('setting the region re-defaults the channel and currency from it', function (): void {
    $regionChannel = Channel::factory()->create(['default' => false]);
    $regionCurrency = Currency::factory()->create(['default' => false, 'code' => 'EUR']);

    $region = Region::factory()->create([
        'default' => false,
        'channel_id' => $regionChannel->id,
        'currency_id' => $regionCurrency->id,
        'language_id' => Language::factory()->create(['default' => true])->id,
    ]);

    /** @var StorefrontSessionManager */
    $manager = app(StorefrontSession::class);
    $manager->setRegion($region);

    expect($manager->getChannel()->id)->toBe($regionChannel->id);
    expect($manager->getCurrency()->code)->toBe('EUR');

    // an explicit currency override still wins afterwards
    $manager->setCurrency(Currency::getDefault());
    expect($manager->getCurrency()->id)->toBe(Currency::getDefault()->id);
});

test('the session currency defaults from the region', function (): void {
    $regionCurrency = Currency::factory()->create(['default' => false, 'code' => 'EUR']);

    Region::factory()->create([
        'default' => true,
        'channel_id' => Channel::getDefault()->id,
        'currency_id' => $regionCurrency->id,
        'language_id' => Language::factory()->create(['default' => true])->id,
    ]);

    /** @var StorefrontSessionManager */
    $manager = app(StorefrontSession::class);

    expect($manager->getCurrency()->code)->toBe('EUR');
});
