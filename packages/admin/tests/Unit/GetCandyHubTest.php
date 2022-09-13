<?php

namespace Lunar\Hub\Tests\Unit;

use Lunar\Hub\Assets\Script;
use Lunar\Hub\Assets\Style;
use Lunar\Hub\LunarHub;
use Lunar\Hub\Tests\TestCase;

/**
 * @group hub
 */
class LunarHubTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function can_register_scripts_by_providing_name_and_path()
    {
        $name = 'local-script';
        $path = 'js/local-script.js';

        LunarHub::script($name, $path);

        $this->assertContainsOnlyInstancesOf(Script::class, LunarHub::scripts());
        $this->assertEquals(LunarHub::scripts()[0]->name(), $name);
        $this->assertEquals(LunarHub::scripts()[0]->path(), $path);

        LunarHub::$scripts = [];
    }

    /** @test */
    public function can_register_remote_scripts()
    {
        $path = 'https://example.com/script.js';

        LunarHub::remoteScript($path);

        $this->assertContainsOnlyInstancesOf(Script::class, LunarHub::scripts());
        $this->assertEquals(LunarHub::scripts()[0]->name(), md5($path));
        $this->assertEquals(LunarHub::scripts()[0]->path(), $path);
        $this->assertEquals(LunarHub::scripts()[0]->isRemote(), true);

        $name = 'remote-script';
        $path2 = 'https://example.com/script2.js';

        LunarHub::script($name, $path2, true);

        $this->assertEquals(LunarHub::scripts()[1]->name(), $name);
        $this->assertEquals(LunarHub::scripts()[1]->path(), $path2);
        $this->assertEquals(LunarHub::scripts()[1]->isRemote(), true);

        LunarHub::$scripts = [];
    }

    /** @test */
    public function can_register_styles_by_providing_name_and_path()
    {
        $name = 'local-style';
        $path = 'local-style.css';

        LunarHub::style($name, $path);

        $this->assertContainsOnlyInstancesOf(Style::class, LunarHub::styles());
        $this->assertEquals(LunarHub::styles()[0]->name(), $name);
        $this->assertEquals(LunarHub::styles()[0]->path(), $path);

        LunarHub::$styles = [];
    }

    /** @test */
    public function can_register_remote_styles()
    {
        $path = 'https://example.com/style.css';

        LunarHub::remoteStyle($path);

        $this->assertContainsOnlyInstancesOf(Style::class, LunarHub::scripts());
        $this->assertEquals(LunarHub::styles()[0]->name(), md5($path));
        $this->assertEquals(LunarHub::styles()[0]->path(), $path);
        $this->assertEquals(LunarHub::styles()[0]->isRemote(), true);

        $name = 'remote-style';
        $path2 = 'https://example.com/style2.css';

        LunarHub::style($name, $path2, true);

        $this->assertEquals(LunarHub::styles()[1]->name(), $name);
        $this->assertEquals(LunarHub::styles()[1]->path(), $path2);
        $this->assertEquals(LunarHub::styles()[1]->isRemote(), true);

        LunarHub::$styles = [];
    }
}
