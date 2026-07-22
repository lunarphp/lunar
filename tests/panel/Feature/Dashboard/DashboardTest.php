<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Models\StaffPreference;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

it('redirects guests away from the dashboard', function () {
    $this->get(route('panel.dashboard'))->assertRedirect(route('panel.login'));
});

it('renders the dashboard with the registered widgets in default order', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $this->get(route('panel.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('range', '30d')
            ->has('widgets', 9)
            ->where('widgets.0.key', 'kpis')
            ->where('widgets.0.span', 'full')
            ->where('widgets.0.flat', true)
            ->where('widgets.0.visible', true)
            ->where('widgets.1.key', 'revenue-chart')
            ->where('widgets.6.key', 'customer-groups')
            ->where('widgets.6.visible', false)
            ->where('widgets.8.key', 'tasks')
            ->where('widgets.8.visible', false)
            ->has('urls.preferences')
        );
});

it('defers widget data for visible widgets only', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Currency::factory()->create(['code' => 'GBP', 'default' => true, 'exchange_rate' => 1]);

    Order::factory()->placed()->create(['total' => 10000, 'exchange_rate' => 1, 'placed_at' => now()]);

    $this->get(route('panel.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->loadDeferredProps(fn (Assert $props) => $props
                ->where('widgetData.kpis.tiles.0.label', 'Revenue')
                ->where('widgetData.kpis.tiles.0.value', '£100.00')
                ->where('widgetData.revenue-chart.total', '£100.00')
                ->where('widgetData.revenue-chart.hasOrders', true)
                ->has('widgetData.recent-orders.orders', 1)
                ->missing('widgetData.customer-groups')
                ->missing('widgetData.tasks')
            )
        );
});

it('scopes widget data to the requested range', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Currency::factory()->create(['code' => 'GBP', 'default' => true, 'exchange_rate' => 1]);

    Order::factory()->placed()->create(['total' => 10000, 'exchange_rate' => 1, 'placed_at' => now()]);
    Order::factory()->placed()->create(['total' => 5000, 'exchange_rate' => 1, 'placed_at' => now()->subDays(20)]);

    $this->get(route('panel.dashboard', ['range' => '7d']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('range', '7d')
            ->loadDeferredProps(fn (Assert $props) => $props
                ->where('widgetData.revenue-chart.total', '£100.00')
                ->has('widgetData.revenue-chart.points', 7)
            )
        );
});

it('falls back to the default range for unknown range values', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $this->get(route('panel.dashboard', ['range' => 'bogus']))
        ->assertInertia(fn (Assert $page) => $page->where('range', '30d'));
});

it('filters widgets by permission for non-admin staff', function () {
    $staff = Staff::factory()->create(['admin' => false]);
    $staff->givePermissionTo('sales:manage-orders');

    $this->actingAs($staff, 'staff');

    $this->get(route('panel.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // top-products, customer-groups and low-stock need permissions
            // this staff member lacks; they are absent, not merely hidden.
            ->has('widgets', 6)
            ->where('widgets.0.key', 'kpis')
            ->missing('widgets.6')
        );
});

it('applies the staff member\'s stored layout order, visibility and range', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    StaffPreference::factory()->for($staff)->create([
        'key' => 'dashboard',
        'value' => [
            'range' => '7d',
            'widgets' => [
                ['key' => 'recent-orders', 'visible' => true],
                ['key' => 'kpis', 'visible' => false],
                ['key' => 'stale-uninstalled-widget', 'visible' => true],
            ],
        ],
    ]);

    $this->actingAs($staff, 'staff');

    $this->get(route('panel.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('range', '7d')
            ->has('widgets', 9)
            // Stored order leads; unknown keys drop out.
            ->where('widgets.0.key', 'recent-orders')
            ->where('widgets.0.visible', true)
            ->where('widgets.1.key', 'kpis')
            ->where('widgets.1.visible', false)
            // Widgets absent from the stored layout append in default order
            // with their default visibility.
            ->where('widgets.2.key', 'revenue-chart')
            ->where('widgets.2.visible', true)
        );
});

it('reports channel revenue segments', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Currency::factory()->create(['code' => 'GBP', 'default' => true, 'exchange_rate' => 1]);

    $web = Channel::factory()->create(['name' => 'Webstore']);
    $pos = Channel::factory()->create(['name' => 'POS']);

    Order::factory()->placed()->create(['channel_id' => $web->id, 'total' => 10000, 'exchange_rate' => 1, 'placed_at' => now()]);
    Order::factory()->placed()->create(['channel_id' => $web->id, 'total' => 5000, 'exchange_rate' => 1, 'placed_at' => now()]);
    Order::factory()->placed()->create(['channel_id' => $pos->id, 'total' => 2500, 'exchange_rate' => 1, 'placed_at' => now()]);

    $this->get(route('panel.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->loadDeferredProps(fn (Assert $props) => $props
                ->where('widgetData.channels.segments.0.label', 'Webstore')
                ->where('widgetData.channels.segments.0.display', '£150.00')
                ->where('widgetData.channels.segments.1.label', 'POS')
                ->where('widgetData.channels.total', '£175.00')
            )
        );
});

it('abbreviates large summary totals while keeping the exact value alongside', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Currency::factory()->create(['code' => 'GBP', 'default' => true, 'exchange_rate' => 1]);

    $web = Channel::factory()->create(['name' => 'Webstore']);

    // Two orders netting GBP 1,398,635.13 in the window.
    Order::factory()->placed()->create(['channel_id' => $web->id, 'total' => 100_000_000, 'exchange_rate' => 1, 'placed_at' => now()]);
    Order::factory()->placed()->create(['channel_id' => $web->id, 'total' => 39_863_513, 'exchange_rate' => 1, 'placed_at' => now()]);

    $this->get(route('panel.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->loadDeferredProps(fn (Assert $props) => $props
                // Donut centre is abbreviated; the exact figure rides along for the tooltip and legend.
                ->where('widgetData.channels.total', '£1.4M')
                ->where('widgetData.channels.totalExact', '£1,398,635.13')
                ->where('widgetData.channels.segments.0.display', '£1,398,635.13')
                // KPI revenue tile abbreviates too, keeping its exact value.
                ->where('widgetData.kpis.tiles.0.value', '£1.4M')
                ->where('widgetData.kpis.tiles.0.valueExact', '£1,398,635.13')
            )
        );
});

it('excludes cancelled orders from revenue', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Currency::factory()->create(['code' => 'GBP', 'default' => true, 'exchange_rate' => 1]);

    Order::factory()->placed()->create(['total' => 10000, 'exchange_rate' => 1, 'placed_at' => now()]);
    Order::factory()->placed()->create(['total' => 99900, 'exchange_rate' => 1, 'placed_at' => now(), 'cancelled_at' => now()]);

    $this->get(route('panel.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->loadDeferredProps(fn (Assert $props) => $props
                ->where('widgetData.revenue-chart.total', '£100.00')
            )
        );
});

it('converts foreign-currency orders at the captured exchange rate', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Currency::factory()->create(['code' => 'GBP', 'default' => true, 'exchange_rate' => 1]);

    Order::factory()->placed()->create(['total' => 12000, 'currency_code' => 'EUR', 'exchange_rate' => 1.2, 'placed_at' => now()]);

    $this->get(route('panel.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->loadDeferredProps(fn (Assert $props) => $props
                ->where('widgetData.revenue-chart.total', '£100.00')
            )
        );
});
