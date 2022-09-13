<?php

namespace Lunar\Hub\Tests\Unit;

use Lunar\Hub\Assets\Script;
use Lunar\Hub\Assets\Style;
use Lunar\Hub\GetCandyHub;
use Lunar\Hub\Tests\TestCase;

/**
 * @group hub
 */
class GetCandyHubTest extends TestCase
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

        GetCandyHub::script($name, $path);

        $this->assertContainsOnlyInstancesOf(Script::class, GetCandyHub::scripts());
        $this->assertEquals(GetCandyHub::scripts()[0]->name(), $name);
        $this->assertEquals(GetCandyHub::scripts()[0]->path(), $path);

        GetCandyHub::$scripts = [];
    }

    /** @test */
    public function can_register_remote_scripts()
    {
        $path = 'https://example.com/script.js';

        GetCandyHub::remoteScript($path);

        $this->assertContainsOnlyInstancesOf(Script::class, GetCandyHub::scripts());
        $this->assertEquals(GetCandyHub::scripts()[0]->name(), md5($path));
        $this->assertEquals(GetCandyHub::scripts()[0]->path(), $path);
        $this->assertEquals(GetCandyHub::scripts()[0]->isRemote(), true);

        $name = 'remote-script';
        $path2 = 'https://example.com/script2.js';

        GetCandyHub::script($name, $path2, true);

        $this->assertEquals(GetCandyHub::scripts()[1]->name(), $name);
        $this->assertEquals(GetCandyHub::scripts()[1]->path(), $path2);
        $this->assertEquals(GetCandyHub::scripts()[1]->isRemote(), true);

        GetCandyHub::$scripts = [];
    }

    /** @test */
    public function can_register_styles_by_providing_name_and_path()
    {
        $name = 'local-style';
        $path = 'local-style.css';

        GetCandyHub::style($name, $path);

        $this->assertContainsOnlyInstancesOf(Style::class, GetCandyHub::styles());
        $this->assertEquals(GetCandyHub::styles()[0]->name(), $name);
        $this->assertEquals(GetCandyHub::styles()[0]->path(), $path);

        GetCandyHub::$styles = [];
    }

    /** @test */
    public function can_register_remote_styles()
    {
        $path = 'https://example.com/style.css';

        GetCandyHub::remoteStyle($path);

        $this->assertContainsOnlyInstancesOf(Style::class, GetCandyHub::scripts());
        $this->assertEquals(GetCandyHub::styles()[0]->name(), md5($path));
        $this->assertEquals(GetCandyHub::styles()[0]->path(), $path);
        $this->assertEquals(GetCandyHub::styles()[0]->isRemote(), true);

        $name = 'remote-style';
        $path2 = 'https://example.com/style2.css';

        GetCandyHub::style($name, $path2, true);

        $this->assertEquals(GetCandyHub::styles()[1]->name(), $name);
        $this->assertEquals(GetCandyHub::styles()[1]->path(), $path2);
        $this->assertEquals(GetCandyHub::styles()[1]->isRemote(), true);

        GetCandyHub::$styles = [];
    }
}
