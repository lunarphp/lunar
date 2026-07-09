<?php

use Lunar\Core\Models\Staff;
use Lunar\Panel\PanelManager;
use Lunar\Tests\Panel\Fixtures\AddonTestCase;

uses(AddonTestCase::class);

it('mounts add-on routes inside the authenticated panel context', function () {
    $this->get('/panel/widgets')->assertRedirect(route('panel.login'));

    $staff = Staff::factory()->create(['admin' => true]);

    $this->actingAs($staff, 'staff')
        ->get('/panel/widgets')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Widgets/Index'));
});

it('shows the add-on navigation to staff', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    $this->actingAs($staff, 'staff')
        ->get('/panel')
        ->assertInertia(fn ($page) => $page
            ->where('navigation.groups', fn ($groups) => collect($groups)
                ->pluck('key')
                ->contains('widgets-group'))
        );
});

it('shares section and extension slots on the add-on page', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    $this->actingAs($staff, 'staff')
        ->get('/panel/widgets')
        ->assertInertia(fn ($page) => $page
            // The zone key itself contains dots ("widgets.index:main:after"), so it can't
            // be reached via Laravel's dot-notation `has`/`where` path assertions — inspect
            // the "slots" prop directly instead.
            ->where('slots', function ($slots) {
                $zone = $slots->get('widgets.index:main:after');

                return count($zone) === 2
                    && $zone[0]['component'] === 'widgets::Banner'
                    && $zone[1]['component'] === 'widgets-extra::Promo';
            })
        );
});

it('resolves the add-on table extension', function () {
    $resolver = app(PanelManager::class)->resolveExtensions('widgets.index');

    expect(array_column($resolver->getColumns(), 'key'))->toBe(['status']);
});

it('registers the add-on vite module', function () {
    $vites = app(PanelManager::class)->registeredVites();

    expect($vites)->toHaveKey('widgets-addon')
        ->and($vites['widgets-addon']['buildDirectory'])->toBe('vendor/lunar-panel/widgets-addon');
});
