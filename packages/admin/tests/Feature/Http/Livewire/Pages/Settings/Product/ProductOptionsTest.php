<?php

namespace Lunar\Hub\Tests\Feature\Http\Livewire\Pages\Settings\Product;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Hub\Models\Staff;
use Lunar\Hub\Tests\TestCase;
use Lunar\Models\Language;

/**
 * @group hub.products
 */
class ProductOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // Resolves the issue with tests failing where livewire components have translations
        Language::factory()->create([
            'default' => true,
            'code' => 'en',
        ]);
    }

    /** @test */
    public function cant_view_page_as_guest()
    {
        $this->get('/hub/settings/product/options')
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

        $this->get('/hub/settings/product/options')
            ->assertSeeLivewire('hub.components.settings.product.options.index');
    }
}
