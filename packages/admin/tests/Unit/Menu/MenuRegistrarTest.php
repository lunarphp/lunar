<?php

namespace Lunar\Hub\Tests\Unit\Menu;

use Lunar\Hub\Menu\MenuRegistry;
use Lunar\Hub\Menu\MenuSlot;
use Lunar\Hub\Tests\TestCase;

/**
 * @group hub.menu
 */
class MenuRegistrarTest extends TestCase
{
    /** @test */
    public function can_fetch_class_from_container()
    {
        $registry = $this->app->make(MenuRegistry::class);

        $this->assertEquals(MenuRegistry::class, get_class($registry));
    }

    /** @test */
    public function instance_is_a_singleton()
    {
        $registryOne = $this->app->make(MenuRegistry::class);
        $registryTwo = $this->app->make(MenuRegistry::class);

        $this->assertEquals(
            spl_object_hash($registryOne),
            spl_object_hash($registryTwo)
        );
    }

    /** @test */
    public function can_add_and_retrieve_a_new_slot()
    {
        $registry = $this->app->make(MenuRegistry::class);

        $registry->slot('foobar');

        $slot = $registry->slot('foobar');

        $this->assertInstanceOf(MenuSlot::class, $slot);
    }
}
