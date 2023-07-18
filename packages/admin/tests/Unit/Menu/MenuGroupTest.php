<?php

namespace Lunar\Hub\Tests\Unit\Menu;

use Lunar\Hub\Menu\MenuGroup;
use Lunar\Hub\Tests\TestCase;

/**
 * @group hub.menu
 */
class MenuGroupTest extends TestCase
{
    /** @test */
    public function can_initialise_a_new_group()
    {
        $group = new MenuGroup('products');

        $group->name('Products');

        $this->assertEquals('Products', $group->name);
    }
}
