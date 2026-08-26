<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Contracts\AttributeCache;
use Lunar\Core\Contracts\CacheInvalidator;
use Lunar\Core\Contracts\CartSession;
use Lunar\Core\Contracts\DiscountManager;
use Lunar\Core\Contracts\StorefrontSession;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Service lifetimes
|--------------------------------------------------------------------------
|
| Services that memoize per-request or per-visitor state must be bound
| `scoped`, not `singleton` — under Octane or a queue worker a singleton
| lives for the process, so one visitor's context bleeds into the next.
| The list below is the canonical set of request-stateful services; adding
| to it should be a conscious choice.
|
*/

beforeEach(function (): void {
    Channel::factory()->create(['default' => true]);
    CustomerGroup::factory()->create(['default' => true]);
    Currency::factory()->create(['default' => true, 'code' => 'GBP']);
});

dataset('scoped services', [
    'cart session' => [CartSession::class],
    'storefront session' => [StorefrontSession::class],
    'discount manager' => [DiscountManager::class],
    'cache invalidator' => [CacheInvalidator::class],
    'attribute cache' => [AttributeCache::class],
    'access control manifest' => ['lunar-access-control'],
]);

test('request-stateful services are memoized within a scope', function (string $abstract) {
    expect(app($abstract))->toBe(app($abstract));
})->with('scoped services');

test('request-stateful services are discarded when the scope ends', function (string $abstract) {
    $first = app($abstract);

    app()->forgetScopedInstances();

    expect(app($abstract))->not->toBe($first);
})->with('scoped services');
