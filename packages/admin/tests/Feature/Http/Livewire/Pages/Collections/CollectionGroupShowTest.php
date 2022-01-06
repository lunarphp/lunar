<?php

namespace GetCandy\Hub\Tests\Feature\Http\Livewire\Pages\Settings\Products;

use GetCandy\Hub\Models\Staff;
use GetCandy\Hub\Tests\TestCase;
use GetCandy\Models\Collection;
use GetCandy\Models\CollectionGroup;
use GetCandy\Models\Currency;
use GetCandy\Models\Language;
use GetCandy\Models\Price;
use GetCandy\Models\Product;
use GetCandy\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group hub.collections
 */
class CollectionGroupShowTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function cant_view_page_as_guest()
    {
        $this->get(route('hub.collection-groups.show', [
            'group' => CollectionGroup::factory()->create(),
        ]))->assertRedirect(
            route('hub.login')
        );
    }

    /** @test */
    public function cant_view_page_without_permission()
    {
        $staff = Staff::factory()->create([
            'admin' => false,
        ]);

        $this->actingAs($staff, 'staff');

        $this->get(route('hub.collection-groups.show', [
            'group' => CollectionGroup::factory()->create(),
        ]))->assertStatus(403);
    }

    /** @test */
    public function can_view_page_with_correct_permission()
    {
        $staff = Staff::factory()->create([
            'admin' => false,
        ]);

        $staff->permissions()->createMany([
            [
                'handle' => 'catalogue:manage-collections',
            ],
        ]);

        $this->actingAs($staff, 'staff');

        $this->get(route('hub.collection-groups.show', [
            'group' => CollectionGroup::factory()->create(),
        ]))->assertSeeLivewire('hub.components.collections.collection-groups.show');
    }
}
