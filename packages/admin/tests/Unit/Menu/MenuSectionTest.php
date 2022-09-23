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
        $section = new MenuSection('Foo Bar');

        $this->assertEquals('Foo Bar', $section->name);
        $this->assertEquals('foo-bar', $section->getHandle());
    }

    /** @test */
    public function can_set_name_on_section()
    {
        $section = new MenuSection('Foo Bar');
        $this->assertEquals('Foo Bar', $section->name);
        $section->name('Another Name');
        $this->assertEquals('Another Name', $section->name);
    }
}
