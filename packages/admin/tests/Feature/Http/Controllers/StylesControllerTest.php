<?php

namespace Lunar\Hub\Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Hub\LunarHub;
use Lunar\Hub\Models\Staff;
use Lunar\Hub\Tests\TestCase;

/**
 * @group hub.assets
 */
class StylesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function fails_when_accessing_non_existing_local_style_name()
    {
        $staff = Staff::factory()->create([
            'admin' => false,
        ]);

        $this->actingAs($staff, 'staff');

        $this->get(route('hub.assets.styles', ['style' => 'non-existing-style']))
            ->assertStatus(404);
    }

    /** @test */
    public function fails_when_accessing_non_existing_script()
    {
        $staff = Staff::factory()->create([
            'admin' => false,
        ]);

        $this->actingAs($staff, 'staff');

        $name = 'non-existing-local-style';
        $path = 'js/non-existing-local-style.css';

        LunarHub::style($name, $path);

        $this->get(route('hub.assets.styles', ['style' => $name]))
            ->assertStatus(500);

        LunarHub::$styles = [];
    }

    /** @test */
    public function can_return_existing_local_script()
    {
        $staff = Staff::factory()->create([
            'admin' => false,
        ]);

        $this->actingAs($staff, 'staff');

        $name = 'local-style';
        $path = __DIR__.'/local-style.css';
        $content = 'body { color: red; }';

        LunarHub::style($name, $path);

        // Create the file
        file_put_contents($path, $content);

        $this->get(route('hub.assets.styles', ['style' => $name]))
            ->assertStatus(200)
            ->assertSee($content);

        // Remove the file after test
        unlink($path);

        LunarHub::$styles = [];
    }
}
