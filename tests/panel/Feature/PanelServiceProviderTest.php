<?php

use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

it('merges the panel config', function () {
    expect(config('lunar.panel.path'))->toBe('panel')
        ->and(config('lunar.panel.guard'))->toBeNull()
        ->and(config('lunar.panel.route_middleware'))->toBe(['web']);
});

it('loads the panel translation namespace', function () {
    expect(__('panel::nav.dashboard'))->toBe('Dashboard');
});

it('does not collide with the filament admin config key', function () {
    // lunar.admin belongs to the Filament admin; lunar.panel to this package.
    expect(config('lunar.panel.enable_variants'))->toBeNull();
});
