<?php

namespace Lunar\Core\Base;

use Composer\InstalledVersions;
use Illuminate\Support\Collection;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;

class TelemetryInsights implements ProvidesTelemetryInsights
{
    public function domainHash(): string
    {
        return md5(
            config('app.url')
        );
    }

    public function environment(): string
    {
        return app()->environment();
    }

    public function laravelVersion(): string
    {
        return app()->version();
    }

    public function lunarVersion(): string
    {
        return InstalledVersions::getPrettyVersion('lunarphp/core') ?? 'dev';
    }

    public function dbDriver(): string
    {
        return config('database.default');
    }

    public function phpVersion(): string
    {
        return phpversion();
    }

    public function productCount(): int
    {
        return Product::count();
    }

    public function productVariantCount(): int
    {
        return ProductVariant::count();
    }

    public function orderCount(): int
    {
        return Order::whereBetween('placed_at', [now()->subHours(24), now()])->count();
    }

    public function orderTotal(): int
    {
        return Order::whereBetween('placed_at', [now()->subHours(24), now()])->sum('total');
    }

    public function currencies(): Collection
    {
        return Currency::where('enabled', true)->get()->map(
            fn (Currency $currency) => $currency->code
        );
    }

    public function languages(): Collection
    {
        return Language::all()->map(
            fn (Language $language) => $language->code
        );
    }
}
