<?php

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Lunar\Panel\Http\Middleware\HandlePanelInertiaRequests;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

it('merges the panel config', function () {
    expect(config('lunar.panel.path'))->toBe('panel')
        ->and(config('lunar.panel.guard'))->toBeNull()
        ->and(config('lunar.panel.route_middleware'))->toBe(['lunar.panel']);
});

it('registers the panel middleware group insulated from the host web group', function () {
    $csrf = class_exists('Illuminate\Foundation\Http\Middleware\PreventRequestForgery')
        ? 'Illuminate\Foundation\Http\Middleware\PreventRequestForgery'
        : 'Illuminate\Foundation\Http\Middleware\ValidateCsrfToken';

    $group = app('router')->getMiddlewareGroups()['lunar.panel'] ?? null;

    expect($group)->toBe([
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        ShareErrorsFromSession::class,
        $csrf,
        SubstituteBindings::class,
    ]);
});

it('does not run an app inertia middleware on panel routes', function () {
    $route = collect(app('router')->getRoutes())
        ->first(fn ($route) => $route->getName() === 'panel.dashboard');

    expect($route->gatherMiddleware())
        ->toContain(HandlePanelInertiaRequests::class)
        ->not->toContain('web');
});

it('loads the panel translation namespace', function () {
    expect(__('panel::nav.dashboard'))->toBe('Dashboard');
});

it('does not collide with the filament admin config key', function () {
    // lunar.admin belongs to the Filament admin; lunar.panel to this package.
    expect(config('lunar.panel.enable_variants'))->toBeNull();
});
