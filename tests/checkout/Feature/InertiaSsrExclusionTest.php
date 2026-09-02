<?php

use Illuminate\Http\Client\StrayRequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Ssr\Gateway;
use Lunar\Tests\Checkout\TestCase;

uses(TestCase::class);

/**
 * The checkout is a self-contained Inertia app with its own root view, bundle
 * and page components. A host running SSR renders from its own entry, which
 * has never heard of the checkout's pages, so an SSR attempt can only fail and
 * fall back to a client render. The provider excludes the checkout paths; these
 * tests pin that so a host does not silently pay a round trip per render.
 */
beforeEach(function (): void {
    config([
        'inertia.ssr.enabled' => true,
        // Otherwise dispatch() short-circuits on a missing bundle and both
        // cases below return null for the wrong reason.
        'inertia.ssr.ensure_bundle_exists' => false,
    ]);

    Http::preventStrayRequests();
});

it('does not dispatch a checkout render to the host ssr server', function (): void {
    $request = Request::create('/checkout/2c1b0f5e-0000-4000-8000-000000000000', 'GET');

    expect(app(Gateway::class)->dispatch(['component' => 'Show'], $request))->toBeNull();
});

it('does not dispatch the checkout entry point to the host ssr server', function (): void {
    $request = Request::create('/checkout', 'POST');

    expect(app(Gateway::class)->dispatch(['component' => 'Show'], $request))->toBeNull();
});

it('leaves the host application ssr untouched', function (): void {
    $request = Request::create('/products/some-product', 'GET');

    expect(fn () => app(Gateway::class)->dispatch(['component' => 'products/Show'], $request))
        ->toThrow(StrayRequestException::class);
});
