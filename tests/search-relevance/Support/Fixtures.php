<?php

namespace Lunar\Tests\SearchRelevance\Support;

use Illuminate\Support\Facades\Config;
use Laravel\Scout\EngineManager;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;

final class Fixtures
{
    /** The defaults the storefront and cart sessions need to boot. */
    public static function storefront(): void
    {
        Language::factory()->create(['default' => true, 'code' => 'en']);
        Channel::factory()->create(['default' => true]);
        Currency::factory()->create(['default' => true]);
        CustomerGroup::factory()->create(['default' => true]);
    }

    /** Route Product through the Database engine with a name-only Scout driver. */
    public static function databaseEngine(): void
    {
        Config::set('scout.driver', 'database');
        Config::set('lunar.search.engine_map', [Product::class => 'database']);

        app(EngineManager::class)->extend('database', fn () => new NameSearchScoutEngine);
    }

    /** @return array<int, Product> in creation order */
    public static function products(int $count, string $prefix = 'cable'): array
    {
        $products = [];

        for ($i = 1; $i <= $count; $i++) {
            $products[] = Product::factory()->create(['name' => collect(['en' => "{$prefix} {$i}"])]);
        }

        return $products;
    }
}
