<?php

namespace Lunar\Hub\Tests\Unit\Http\Livewire\Components;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Lunar\DataTypes\ShippingOption;
use Lunar\Hub\Http\Livewire\Dashboard;
use Lunar\Hub\Models\Staff;
use Lunar\Hub\Tests\TestCase;
use Lunar\Models\Currency;
use Lunar\Models\OrderAddress;
use Lunar\Models\OrderLine;

/**
 * @group hub.dashboard
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_mount_component()
    {
        $staff = Staff::factory()->create([
            'email' => 'test@domain.com',
            'firstname' => 'Bob',
            'lastname' => 'Smith',
        ]);

        Currency::factory()->create();

        $line = OrderLine::factory()->create([
            'type' => 'shipping',
            'purchasable_type' => ShippingOption::class,
        ]);

        OrderAddress::factory()->create([
            'type' => 'billing',
            'order_id' => $line->order_id,
        ]);

        Livewire::test(Dashboard::class, ['staff' => $staff])
            ->assertHasNoErrors();
    }
}
