<?php

namespace Lunar\Hub\Tests\Unit\Menu;

use Lunar\Hub\Menu\MenuSection;
use Lunar\Hub\Tests\TestCase;

/**
 * @group hub.menu
 */
class MenuSectionTest extends TestCase
{
    /** @test */
    public function can_initialise_a_new_section()
    {
        $section = new MenuSection('products');

        $section->name('Products');

        $this->assertEquals('Products', $section->name);
    }
}
