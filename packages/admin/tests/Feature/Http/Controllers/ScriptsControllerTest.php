<?php

namespace GetCandy\Hub\Tests\Feature\Http\Controllers;

use GetCandy\Hub\GetCandyHub;
use GetCandy\Hub\Models\Staff;
use GetCandy\Hub\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group hub.assets
 */
class ScriptsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function fails_when_accessing_non_existing_local_script_name()
    {
        $staff = Staff::factory()->create([
            'admin' => false,
        ]);

        $this->actingAs($staff, 'staff');

        $this->get(route('hub.assets.scripts', ['script' => 'non-existing-script']))
            ->assertStatus(404);
    }

    /** @test */
    public function fails_when_accessing_non_existing_script()
    {
        $staff = Staff::factory()->create([
            'admin' => false,
        ]);

        $this->actingAs($staff, 'staff');

        $name = 'non-existing-local-script';
        $path = 'js/non-existing-local-script.js';

        GetCandyHub::script($name, $path);

        $this->get(route('hub.assets.scripts', ['script' => $name]))
            ->assertStatus(500);

        GetCandyHub::$scripts = [];
    }

    /** @test */
    public function can_return_existing_local_script()
    {
        $staff = Staff::factory()->create([
            'admin' => false,
        ]);

        $this->actingAs($staff, 'staff');

        $name = 'local-script';
        $path = __DIR__.'/local-script.js';
        $content = "console.log('hello');";

        GetCandyHub::script($name, $path);

        // Create the file
        file_put_contents($path, $content);

        $this->get(route('hub.assets.scripts', ['script' => $name]))
            ->assertStatus(200)
            ->assertSee($content, false);

        // Remove the file after test
        unlink($path);

        GetCandyHub::$scripts = [];
    }
}
