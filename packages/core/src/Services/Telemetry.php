<?php

namespace Lunar\Services;

use Composer\InstalledVersions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Order;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

class Telemetry
{
    public static function run(): void
    {
        //        Cache::remember('lunar_telemetry', 86400, function () {
        Http::post('https://lunarstats.test/api/stats', [
            'domain_hash' => md5(request()->getHost()),
            'environment' => app()->environment(),
            'laravel_version' => app()->version(),
            'lunar_version' => InstalledVersions::getPrettyVersion('lunarphp/core'),
            'db_driver' => config('database.default'),
            'php_version' => phpversion(),
            'product_count' => Product::count(),
            'variant_count' => ProductVariant::count(),
            'order_count' => Order::whereBetween('placed_at', [now()->subHours(24), now()])->count(),
            'order_total' => Order::whereBetween('placed_at', [now()->subHours(24), now()])->sum('total'),
            'currency' => Currency::getDefault()?->code,
            'language' => Language::getDefault()?->code,
        ]);
        //            return true;
        //        });
    }
}
