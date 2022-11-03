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

        $section
            ->name('Products')
            ->route('hub.products.index')
            ->icon('shopping-bag');

        $this->assertEquals('Products', $section->name);
        $this->assertEquals('hub.products.index', $section->route);
        $this->assertEquals('shopping-bag', $section->icon);
        $this->assertEquals('products', $section->getHandle());
    }
}
