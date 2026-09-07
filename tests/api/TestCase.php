<?php

namespace Lunar\Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\Api\ApiServiceProvider;
use Lunar\Api\Models\ApiKey;
use Lunar\Core\Facades\Taxes;
use Lunar\Core\LunarServiceProvider;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Staff;
use Lunar\Nestedset\NestedSetServiceProvider;
use Lunar\Tests\Core\Stubs\TestTaxDriver;
use Lunar\Tests\Core\Stubs\User;
use Lunar\Tests\TestCase as BaseTestCase;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelBlink\BlinkServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('auth.providers.users.model', User::class);
        Config::set('lunar.taxes.driver', 'test');

        Taxes::extend('test', fn ($app) => $app->make(TestTaxDriver::class));

        activity()->disableLogging();

        $this->freezeTime();
    }

    protected function getPackageProviders($app): array
    {
        return [
            LunarServiceProvider::class,
            ApiServiceProvider::class,
            MediaLibraryServiceProvider::class,
            ActivitylogServiceProvider::class,
            NestedSetServiceProvider::class,
            BlinkServiceProvider::class,
            PermissionServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('auth.providers.users.model', User::class);
    }

    /**
     * The store defaults every storefront request resolves against.
     *
     * @return array{channel: Channel, currency: Currency, language: Language, group: CustomerGroup}
     */
    protected function setUpStore(): array
    {
        return [
            'channel' => Channel::factory()->create(['default' => true, 'handle' => 'webstore']),
            'currency' => Currency::factory()->create(['default' => true, 'code' => 'GBP', 'decimal_places' => 2]),
            'language' => Language::factory()->create(['default' => true, 'code' => 'en']),
            'group' => CustomerGroup::factory()->create(['default' => true, 'handle' => 'retail']),
        ];
    }

    /** A published product scheduled into the store's channel and visible to its default group. */
    protected function visibleProduct(array $store, array $attributes = []): Product
    {
        $product = Product::factory()->create($attributes);
        $product->scheduleChannel($store['channel']);
        $product->scheduleCustomerGroup($store['group'], pivotData: ['enabled' => true, 'visible' => true]);

        return $product->fresh();
    }

    /** A variant with a base price in the given currency. */
    protected function pricedVariant(Product $product, Currency $currency, int $amount, array $attributes = []): ProductVariant
    {
        $variant = ProductVariant::factory()->create(['product_id' => $product->id] + $attributes);

        Price::factory()->create([
            'priceable_type' => $variant->getMorphClass(),
            'priceable_id' => $variant->id,
            'currency_id' => $currency->id,
            'price' => $amount,
            'min_quantity' => 1,
            'customer_group_id' => null,
        ]);

        return $variant;
    }

    /**
     * An issued admin key and the headers that present it.
     *
     * @param  array<int, string>  $abilities
     * @return array{key: ApiKey, token: string, headers: array<string, string>}
     */
    protected function apiKey(array $abilities = ['*'], ?Staff $staff = null): array
    {
        $issued = ApiKey::generate('Test key', $abilities, $staff);

        return [
            'key' => $issued->key,
            'token' => $issued->plainTextToken,
            'headers' => ['Authorization' => 'Bearer '.$issued->plainTextToken],
        ];
    }

    /**
     * Start the next request as a new process would: scoped services and the
     * in-memory session do not carry over between real API requests.
     */
    protected function freshRequest(): static
    {
        $this->flushHeaders();
        $this->app->forgetScopedInstances();
        $this->app['session']->flush();

        // Routes memoize their controller instance; a new process would not have one.
        foreach ($this->app['router']->getRoutes() as $route) {
            $route->flushController();
        }

        return $this;
    }
}
