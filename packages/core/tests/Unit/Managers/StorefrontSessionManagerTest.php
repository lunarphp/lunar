<?php

namespace Lunar\Tests\Unit\Managers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Managers\CartSessionManager;
use Lunar\Tests\TestCase;
use Lunar\Base\StorefrontSessionInterface;
use Lunar\Managers\StorefrontSessionManager;
use Lunar\Models\Channel;
use Lunar\Models\CustomerGroup;

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
}
