<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Staff;
use Lunar\Panel\PanelManager;
use Lunar\Tests\Shipping\PanelTestCase;

uses(PanelTestCase::class)->group('shipping', 'shipping-panel');

it('shows the shipping settings group to staff who can manage shipping', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    $this->actingAs($staff, 'staff')
        ->get(route('panel.settings.shipping.zones.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('settingsNavigation.groups', function ($groups) {
                $group = collect($groups)->firstWhere('key', 'shipping');

                return $group !== null
                    && $group['label'] === 'Shipping'
                    && collect($group['items'])->pluck('key')->all() === ['shipping-zones', 'shipping-methods', 'shipping-exclusion-lists'];
            })
        );
});

it('hides the group and denies the routes to staff without the permission', function () {
    $staff = Staff::factory()->create(['admin' => false]);

    $this->actingAs($staff, 'staff');

    $this->get(route('panel.settings.shipping.zones.index'))->assertForbidden();
    $this->get(route('panel.settings.shipping.methods.index'))->assertForbidden();
    $this->get(route('panel.settings.shipping.exclusion-lists.index'))->assertForbidden();

    $this->get(route('panel.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('settingsNavigation.groups', fn ($groups) => ! collect($groups)->pluck('key')->contains('shipping'))
        );
});

it('grants the routes through the shipping:manage permission alone', function () {
    $staff = Staff::factory()->create(['admin' => false]);
    $staff->givePermissionTo('shipping:manage');

    $this->actingAs($staff, 'staff')
        ->get(route('panel.settings.shipping.zones.index'))
        ->assertOk();
});

it('registers its Vite module and lang namespace with the panel', function () {
    $vites = app(PanelManager::class)->registeredVites();

    expect($vites)->toHaveKey('shipping')
        ->and($vites['shipping']['buildDirectory'])->toBe('vendor/lunar-panel/shipping')
        ->and($vites['shipping']['input'])->toBe('resources/js/panel.ts')
        ->and(app(PanelManager::class)->viteBuildPaths()['shipping'])->toEndWith('packages/table-rate-shipping/build')
        ->and(app(PanelManager::class)->translationNamespaces())->toContain('shipping');
});

it('serves the shipping lang groups through the panel translations endpoint', function () {
    $this->get('/panel/translations/en')
        ->assertOk()
        ->assertJsonPath('messages.shipping::zones.title', 'Shipping zones')
        ->assertJsonPath('messages.shipping::nav.shipping', 'Shipping');
});
