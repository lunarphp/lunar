<?php

namespace Lunar\Base;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TelemetryService implements TelemetryServiceInterface
{
    /**
     * Whether we should be running telemetry
     */
    protected bool $shouldRun = true;

    public function __construct(
        protected ProvidesTelemetryInsights $insights,
    ) {}

    public function optOut(): void
    {
        $this->shouldRun = false;
    }

    public function getInsightsUrl(): string
    {
        return 'https://stats.lunarphp.io/api/insights';
    }

    public function getCacheKey(): string
    {
        return 'lunar_telemetry_last_attempt';
    }

    public function shouldRun(): bool
    {
        if (! $this->shouldRun) {
            return false;
        }

        $lastAttempt = Cache::get($this->getCacheKey());

        return ! $lastAttempt || ! now()->parse($lastAttempt)->isToday();
    }

    public function getInsightsPayload(): array
    {
        return [
            'domain_hash' => $this->insights->domainHash(),
            'environment' => $this->insights->environment(),
            'laravel_version' => $this->insights->laravelVersion(),
            'lunar_version' => $this->insights->lunarVersion(),
            'db_driver' => $this->insights->dbDriver(),
            'php_version' => $this->insights->phpVersion(),
            'product_count' => $this->insights->productCount(),
            'variant_count' => $this->insights->productVariantCount(),
            'order_count' => $this->insights->orderCount(),
            'order_total' => $this->insights->orderTotal(),
            'currencies' => $this->insights->currencies()->join(','),
            'languages' => $this->insights->languages()->join(','),
        ];
    }

    public function run(): void
    {
        if (! $this->shouldRun()) {
            return;
        }

        Cache::forget($this->getCacheKey());

        Cache::remember($this->getCacheKey(), 86400, function (): ?Carbon {
            $response = Http::withHeader('Accept', 'application/json')
                ->timeout(3)
                ->retry(3, 100)
                ->post($this->getInsightsUrl(), $this->getInsightsPayload());

            return $response->successful() ? now() : null;
        });
    }
}
