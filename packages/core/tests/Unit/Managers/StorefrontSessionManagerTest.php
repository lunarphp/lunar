<?php

namespace Lunar\Tests\Unit\Managers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Lunar\Base\StorefrontSessionInterface;
use Lunar\Managers\StorefrontSessionManager;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Lunar\Tests\Stubs\User as StubUser;
use Lunar\Tests\TestCase;

/**
 * @group lunar.storefront-session-manager
 */
class StorefrontSessionManagerTest extends TestCase
{
    use RefreshDatabase;

    private function setAuthUserConfig()
    {
        Config::set('auth.providers.users.model', 'Lunar\Tests\Stubs\User');
    }

    /**
     * @test
     */
    public function can_instantiate_manager()
    {
        Channel::factory()->create([
            'default' => true,
        ]);

        CustomerGroup::factory()->create([
            'default' => true,
        ]);

        $manager = app(StorefrontSessionInterface::class);
        $this->assertInstanceOf(StorefrontSessionManager::class, $manager);
    }

    /**
     * @test
     */
    public function can_initialise_customer_groups()
    {
        Channel::factory()->create([
            'default' => true,
        ]);

        CustomerGroup::factory()->create([
            'default' => true,
        ]);

        $manager = app(StorefrontSessionInterface::class);

        $this->assertCount(1, $manager->getCustomerGroups());
    }

    /**
     * @test
     */
    public function can_initialise_the_channel()
    {
        $channel = Channel::factory()->create([
            'default' => true,
        ]);

        $manager = app(StorefrontSessionInterface::class);

        $this->assertEquals($channel->id, $manager->getChannel()->id);
    }

    /**
     * @test
     */
    public function can_initialise_the_currency()
    {
        Channel::factory()->create([
            'default' => true,
        ]);

        $currency = Currency::factory()->create();

        $manager = app(StorefrontSessionInterface::class);

        $this->assertEquals($currency->id, $manager->getCurrency()->id);
    }

    /**
     * @test
     */
    public function can_initialise_the_customer()
    {
        Channel::factory()->create([
            'default' => true,
        ]);

        $this->setAuthUserConfig();

        $user = StubUser::factory()->create();

        $this->actingAs($user);

        $customers = Customer::factory(5)->create();

        $user->customers()->sync($customers->pluck('id'));

        $this->assertCount(5, $user->customers()->get());

        $this->assertDatabaseCount((new Customer)->getTable(), 5);

        $manager = app(StorefrontSessionInterface::class);

        $this->assertEquals($customers->last()->id, $manager->getCustomer()->id);
    }

    /**
     * @test
     */
    public function can_set_channel()
    {
        Channel::factory()->create([
            'default' => true,
        ]);

        $channelB = Channel::factory()->create([
            'default' => false,
        ]);

        $manager = app(StorefrontSessionInterface::class);

        $manager->setChannel($channelB);

        $this->assertEquals($channelB->id, $manager->getChannel()->id);
    }

    /**
     * @test
     */
    public function can_set_currency()
    {
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

        $this->assertEquals($currencyB->id, $manager->getCurrency()->id);
    }

    /**
     * @test
     */
    public function can_set_customer_groups()
    {
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

        $this->assertEquals($groupB->id, $manager->getCustomerGroups()->first()->id);

        $this->assertEquals(
            [$groupB->handle],
            Session::get(
                $manager->getSessionKey() . '_customer_groups'
            )
        );

        $this->assertCount(1, $manager->getCustomerGroups());
    }

    /**
     * @test
     */
    public function can_set_customer()
    {
        Channel::factory()->create([
            'default' => true,
        ]);

        $this->setAuthUserConfig();

        $user = StubUser::factory()->create();

        $this->actingAs($user);

        $customers = Customer::factory(5)->create();

        $user->customers()->sync($customers->pluck('id'));

        $manager = app(StorefrontSessionInterface::class);

        $customer = $customers->first();

        $manager->setCustomer($customer);

        $this->assertEquals($customer->id, $manager->getCustomer()->id);
    }
}
