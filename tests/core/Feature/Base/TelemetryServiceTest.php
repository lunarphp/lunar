<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Lunar\Base\TelemetryInsights;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class)->group('telemetry');

test('can opt out of telemetry', function () {
    expect(\Lunar\Facades\Telemetry::shouldRun())->toBeTrue();

    \Lunar\Facades\Telemetry::optOut();

    expect(\Lunar\Facades\Telemetry::shouldRun())->toBeFalse();
});

test('can only run once a day', function () {
    \Illuminate\Support\Facades\Http::fake();

    \Illuminate\Support\Facades\Cache::set(\Lunar\Facades\Telemetry::getCacheKey(), now());

    expect(\Lunar\Facades\Telemetry::shouldRun())->toBeFalse();

    \Illuminate\Support\Facades\Cache::set(\Lunar\Facades\Telemetry::getCacheKey(), now()->subDay());

    expect(\Lunar\Facades\Telemetry::shouldRun())->toBeTrue();
});

test('can send insights', function () {
    \Illuminate\Support\Facades\Http::fake();

    app()->singleton(\Lunar\Base\ProvidesTelemetryInsights::class, function () {
        return new class extends TelemetryInsights
        {
            public function lunarVersion(): string
            {
                return '1.0.0';
            }
        };
    });

    \Lunar\Facades\Telemetry::run();

    \Illuminate\Support\Facades\Http::assertSent(function (\Illuminate\Http\Client\Request $request) {
        return $request->method() === 'POST' && $request->url() === \Lunar\Facades\Telemetry::getInsightsUrl();
    });
});

test('can send correct insights payload', function () {
    \Illuminate\Support\Facades\Http::fake();

    app()->singleton(\Lunar\Base\ProvidesTelemetryInsights::class, function () {
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

            public function currencies(): \Illuminate\Support\Collection
            {
                return collect(['GBP', 'USD']);
            }

            public function languages(): \Illuminate\Support\Collection
            {
                return collect(['EN', 'FR']);
            }
        };
    });

    \Lunar\Facades\Telemetry::run();

    \Illuminate\Support\Facades\Http::assertSent(function (\Illuminate\Http\Client\Request $request) {
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
