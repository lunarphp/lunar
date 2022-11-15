<?php

namespace Lunar\Hub\Tests\Unit\Menu;

use Lunar\Hub\Menu\MenuLink;
use Lunar\Hub\Tests\TestCase;

/**
 * @group hub.menu
 */
class MenuLinkTest extends TestCase
{
    /** @test */
    public function can_initialise_an_empty_menu_link()
    {
        $menuLink = new MenuLink();

        $properties = ['name', 'handle', 'gate', 'icon', 'route'];

        foreach ($properties as $property) {
            $this->assertNull($menuLink->{$property});
        }
    }

    /** @test */
    public function can_set_properties_on_menu_link()
    {
        $menuLink = new MenuLink();

        $properties = [
            'name' => 'Foo',
            'handle' => 'foo',
            'gate' => 'foo.gate',
            'icon' => 'cog',
            'route' => 'some.route',
        ];

        foreach ($properties as $property => $value) {
            $menuLink->{$property}($value);
            $this->assertEquals($value, $menuLink->{$property});
        }
    }

    /** @test */
    public function can_render_icon_svg()
    {
        $menuLink = new MenuLink();

        $menuLink->icon('archive');

        $svg = $menuLink->renderIcon();

        $this->assertStringStartsWith('<svg', $svg);
    }

    /** @test */
    public function can_render_custom_svg()
    {
        $menuLink = new MenuLink();

        $menuLink->icon('<svg></svg>');

        $svg = $menuLink->renderIcon();

        $this->assertEquals('<svg></svg>', $svg);
    }

    /** @test */
    public function active_state_is_correct_based_on_given_path()
    {
        $menuLink = new MenuLink();

        $menuLink->handle('foo');

        $this->assertFalse($menuLink->isActive('hub/bar'));
        $this->assertTrue($menuLink->isActive('foo'));
        $this->assertTrue($menuLink->isActive('foo/bar'));
    }
}
