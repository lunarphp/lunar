<?php

namespace Lunar\Tests\Unit\Observers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Lunar\Jobs\Prices\DispatchPriceConversionOnPriceUpdate;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Tests\TestCase;

/**
 * @group observers
 */
class PriceObserverTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // price auto-conversion config
        Config::set([
            'lunar.pricing.auto_conversion.enabled' => true,
            'lunar.pricing.auto_conversion.price_update_job' => DispatchPriceConversionOnPriceUpdate::class,
        ]);
    }

    /** @test */
    public function dispatches_auto_price_conversion_job_on_price_update()
    {
        Bus::fake();

        $currency = Currency::factory()->create(['default' => false]);
        $purchasable = ProductVariant::factory()->create();

        $price = Price::factory()->create([
            'price' => 100,
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => $purchasable->getMorphClass(),
            'priceable_id' => $purchasable->id,
        ]);

        Bus::assertNotDispatched(DispatchPriceConversionOnPriceUpdate::class);

        $currency->update(['default' => true]);
        $price->refresh()->update([
            'price' => 100,
            'tier' => 2,
        ]);

        Bus::assertNotDispatched(DispatchPriceConversionOnPriceUpdate::class);

        $price->update(['price' => 110]);

        Bus::assertDispatched(function (DispatchPriceConversionOnPriceUpdate $job) use ($price) {
            return $job->savedPrice->id === $price->id;
        });
    }
}
