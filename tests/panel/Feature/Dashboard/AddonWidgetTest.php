<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\Fixtures\AddonTestCase;

uses(AddonTestCase::class);

it('registers an add-on dashboard widget through a section extension', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $this->get(route('panel.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('widgets', 10)
            // Anchored after the first-party kpis widget.
            ->where('widgets.0.key', 'kpis')
            ->where('widgets.1.key', 'addon-sales')
            ->where('widgets.1.component', 'widgets::SalesWidget')
            ->where('widgets.1.label', 'Add-on sales')
            ->where('widgets.1.visible', true)
        );
});

it('defers add-on widget data like any first-party widget', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $this->get(route('panel.dashboard', ['range' => '7d']))
        ->assertInertia(fn (Assert $page) => $page
            ->loadDeferredProps(fn (Assert $props) => $props
                ->where('widgetData.addon-sales.message', 'Sales for 7d')
            )
        );
});
