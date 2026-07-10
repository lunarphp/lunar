<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Address;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\Staff;
use Lunar\Panel\PanelManager;
use Lunar\Tests\Panel\Fixtures\ExampleAddonTestCase;

uses(ExampleAddonTestCase::class);

it('renders the example add-on own page for an authenticated admin', function () {
    $this->get('/panel/example-addon')->assertRedirect(route('panel.login'));

    $staff = Staff::factory()->create(['admin' => true]);

    $this->actingAs($staff, 'staff')
        ->get('/panel/example-addon')
        ->assertOk()
        // shouldExist: false — the component is resolved client-side via
        // window.LunarPanel.registerPages(), not a namespaced Blade view, so
        // Inertia's testing view-finder has no on-disk path to check.
        ->assertInertia(fn (Assert $page) => $page->component('example-addon::Widgets/Index', false));
});

it('shows the example add-on navigation to staff', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    $this->actingAs($staff, 'staff')
        ->get('/panel')
        ->assertInertia(fn (Assert $page) => $page
            ->where('navigation.groups', fn ($groups) => collect($groups)
                ->pluck('key')
                ->contains('example-addon-group'))
        );
});

it('merges the example add-on table extension column onto the real customer index', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();
    Address::factory()->count(2)->for($customer)->create();

    $this->get(route('panel.customers.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('columns', function ($columns) {
                $keys = collect($columns)->pluck('key')->all();

                // First-party columns come first; the example add-on's
                // ExampleColumn ("id") is appended by the table extension resolver.
                return $keys === ['full_name', 'company_name', 'customer_groups', 'created_at', 'id'];
            })
            ->where('customers.data.0.id', $customer->id)
        );
});

it('shares the example add-on slot entry on the real customer edit page', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();

    $this->get(route('panel.customers.edit', $customer))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // The zone key contains dots ("customers.edit:main:after"), so it
            // can't be reached via dot-notation where()/has() path assertions.
            ->where('slots', function ($slots) {
                $zone = $slots->get('customers.edit:main:after');

                return $zone !== null
                    && collect($zone)->contains(
                        fn ($entry) => $entry['component'] === 'example-addon::InfoBanner'
                    );
            })
        );
});

it('resolves the example add-on table extension directly', function () {
    $resolver = app(PanelManager::class)->resolveExtensions('customers.index');

    expect(array_column($resolver->getColumns(), 'key'))->toBe(['id']);
});

it('registers the example add-on vite module', function () {
    $vites = app(PanelManager::class)->registeredVites();

    expect($vites)->toHaveKey('example-addon')
        ->and($vites['example-addon']['buildDirectory'])->toBe('vendor/lunar-panel/example-addon');
});
