<?php

namespace GetCandy\Hub\Tests\Feature\Http\Controllers;

use GetCandy\Hub\GetCandyHub;
use GetCandy\Hub\Models\Staff;
use GetCandy\Hub\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

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

        GetCandyHub::style($name, $path);

        $this->get(route('hub.assets.styles', ['style' => $name]))
            ->assertStatus(500);
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

        GetCandyHub::style($name, $path);

        // Create the file
        file_put_contents($path, 'body { color: red; }');

        $this->get(route('hub.assets.styles', ['style' => $name]))
            ->assertStatus(200);

        // Remove the file after test
        unlink($path);
    }
}
