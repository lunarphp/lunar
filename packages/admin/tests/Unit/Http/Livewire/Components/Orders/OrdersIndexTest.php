<?php

namespace GetCandy\Hub\Tests\Unit\Http\Livewire\Components;

use GetCandy\Hub\DataTransferObjects\Search\Facets;
use GetCandy\Hub\DataTransferObjects\Search\SearchResults;
use GetCandy\Hub\Http\Livewire\Components\Orders\OrdersIndex;
use GetCandy\Hub\Models\SavedSearch;
use GetCandy\Hub\Models\Staff;
use GetCandy\Hub\Search\OrderSearch;
use GetCandy\Hub\Tests\TestCase;
use GetCandy\Models\Currency;
use GetCandy\Models\Language;
use GetCandy\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Livewire;
use Mockery;

/**
 * @group hub.orders
 */
class OrdersIndexTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Currency::factory()->create([
            'default' => true,
        ]);

        Language::factory()->create([
            'default' => true,
        ]);
    }

    /** @test */
    public function can_mount_component()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        Livewire::actingAs($staff, 'staff')->test(OrdersIndex::class);
    }
}
