<?php

namespace Lunar\Tests\Unit\Managers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Lunar\Managers\CartSessionManager;
use Lunar\Tests\TestCase;
use Lunar\Base\StorefrontSessionInterface;
use Lunar\Managers\StorefrontSessionManager;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;

use function Ramsey\Uuid\v1;

/**
 * @group lunar.storefront-session-manager
 */
class StorefrontSessionManagerTest extends TestCase
{
    use RefreshDatabase;

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
}
