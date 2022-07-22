<?php

namespace GetCandy\Hub\Tests\Feature\Http\Livewire\Pages\Settings\Attributes;

use GetCandy\Hub\Models\Staff;
use GetCandy\Hub\Tests\TestCase;
use GetCandy\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group attributes
 */
class AttributesShowTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Language::factory()->create([
            'default' => true,
            'code'    => 'en',
        ]);
    }

    /** @test */
    public function cant_view_page_as_guest()
    {
        $this->get('/hub/settings/attributes/*')
            ->assertRedirect(
                route('hub.login')
            );
    }

    /** @test */
    public function can_view_page_when_authenticated()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $this->actingAs($staff, 'staff');

        $this->get('/hub/settings/attributes/product')
            ->assertSeeLivewire('hub.components.settings.attributes.show');
    }
}
