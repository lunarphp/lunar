<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Lunar\Base\ProvidesTelemetryInsights;
use Lunar\Base\TelemetryInsights;
use Lunar\Facades\Telemetry;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class)->group('telemetry');

test('can opt out of telemetry', function () {
    expect(Telemetry::shouldRun())->toBeTrue();

    Telemetry::optOut();

    expect(Telemetry::shouldRun())->toBeFalse();
});

test('skips telemetry when the cache store does not persist', function () {
    config()->set('cache.default', 'null');

    expect(Telemetry::shouldRun())->toBeFalse();
});

test('records the attempt before the HTTP call so failures still throttle', function () {
    Http::fake([
        Telemetry::getInsightsUrl() => Http::response(null, 500),
    ]);

    expect(Telemetry::shouldRun())->toBeTrue();

    Telemetry::run();

    expect(Telemetry::shouldRun())->toBeFalse();
});

test('does not propagate exceptions from the HTTP client', function () {
    Http::fake(function () {
        throw new RuntimeException('connection refused');
    });

    Telemetry::run();

    expect(Telemetry::shouldRun())->toBeFalse();
});

test('can only run once a day', function () {
    Http::fake();

    Cache::set(Telemetry::getCacheKey(), now());

    expect(Telemetry::shouldRun())->toBeFalse();

    Cache::set(Telemetry::getCacheKey(), now()->subDay());

    expect(Telemetry::shouldRun())->toBeTrue();
});

test('can send insights', function () {
    Http::fake();

    app()->singleton(ProvidesTelemetryInsights::class, function () {
        return new class extends TelemetryInsights
        {
            public function lunarVersion(): string
            {
                return '1.0.0';
            }
        };
    });

    Telemetry::run();

    Http::assertSent(function (Request $request) {
        return $request->method() === 'POST' && $request->url() === Telemetry::getInsightsUrl();
    });
});

test('can send correct insights payload', function () {
    Http::fake();

    app()->singleton(ProvidesTelemetryInsights::class, function () {
        return new class extends TelemetryInsights
        {
            public function domainHash(): string
            {
                return 'ABCDEFGHIJKLMNOPQRSTUVXYZ';
            }

            public function dbDriver(): string
            {
                return 'mysql';
            }

            public function laravelVersion(): string
            {
                return '12.0.0';
            }

            public function environment(): string
            {
                return 'production';
            }

            public function lunarVersion(): string
            {
                return '1.0.0';
            }

            public function phpVersion(): string
            {
                return '8.4';
            }

            public function productCount(): int
            {
                return 10;
            }

            public function productVariantCount(): int
            {
                return 50;
            }

            public function orderCount(): int
            {
                return 1000;
            }

            public function orderTotal(): int
            {
                return 50000;
            }

            public function currencies(): Collection
            {
                return collect(['GBP', 'USD']);
            }

            public function languages(): Collection
            {
                return collect(['EN', 'FR']);
            }
        };
    });

    Telemetry::run();

    Http::assertSent(function (Request $request) {
        return
            $request['domain_hash'] == 'ABCDEFGHIJKLMNOPQRSTUVXYZ' &&
            $request['environment'] == 'production' &&
            $request['laravel_version'] == '12.0.0' &&
            $request['lunar_version'] == '1.0.0' &&
            $request['db_driver'] == 'mysql' &&
            $request['php_version'] == '8.4' &&
            $request['product_count'] == 10 &&
            $request['variant_count'] == 50 &&
            $request['order_count'] == 1000 &&
            $request['order_total'] == 50000 &&
            $request['currencies'] == 'GBP,USD' &&
            $request['languages'] == 'EN,FR';
    });
});
