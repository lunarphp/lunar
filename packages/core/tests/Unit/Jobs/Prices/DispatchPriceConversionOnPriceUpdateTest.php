<?php

namespace Lunar\Jobs\Prices;

use Illuminate\Bus\PendingBatch;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Tests\TestCase;

class DispatchPriceConversionOnPriceUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // price auto-conversion config
        Config::set([
            'lunar.pricing.auto_conversion.enabled' => true,
        ]);
    }

    /** @test */
    public function dispatches_price_conversion_job_batch_on_price_update()
    {
        Bus::fake([ConvertPrices::class]);

        $currencies = Currency::factory()
            ->count(2)
            ->sequence(
                ['default' => true],
                ['default' => false]
            )
            ->create();

        $purchasable = ProductVariant::factory()->create();

        $prices = Price::factory()
            ->count(2)
            ->sequence(fn(Sequence $sequence) => [
                'currency_id' => $currencies[$sequence->index % 2]->id,
            ])
            ->create([
                'priceable_type' => $purchasable->getMorphClass(),
                'priceable_id' => $purchasable->id,
            ]);

        DispatchPriceConversionOnPriceUpdate::dispatchSync($prices[0]);

        Bus::assertBatched(function (PendingBatch $batch) {
            return in_array('Price Conversion', $batch->options['tags'], true);
        });
    }

    /** @test */
    public function can_correctly_convert_prices_on_default_price_update()
    {
        $currencies = Currency::factory()
            ->count(4)
            ->sequence(fn(Sequence $sequence) => match ($sequence->index) {
                0 => [
                    'default' => true,
                    'decimal_places' => 2,
                    'exchange_rate' => 1,
                ],
                1 => [
                    'default' => false,
                    'decimal_places' => 2,
                    'exchange_rate' => 1.251,
                ],
                2 => [
                    'default' => false,
                    'decimal_places' => 3,
                    'exchange_rate' => 5.34,
                ],
                3 => [
                    'default' => false,
                    'decimal_places' => 4,
                    'exchange_rate' => 0.953,
                ],
            })
            ->create();

        $purchasable = ProductVariant::factory()->create();

        $prices = Price::factory()
            ->count(4)
            ->sequence(fn(Sequence $sequence) => [
                'currency_id' => $currencies[$sequence->index]->id,
            ])
            ->create([
                'price' => 2145,
                'tier' => 1,
                'priceable_type' => $purchasable->getMorphClass(),
                'priceable_id' => $purchasable->id,
            ]);

        DispatchPriceConversionOnPriceUpdate::dispatchSync($prices[0]);

        $updatedPrices = $prices->fresh();

        self::assertEquals(26.83, $updatedPrices[1]->price->decimal);
        self::assertEquals(114.543, $updatedPrices[2]->price->decimal);
        self::assertEquals(20.4418, $updatedPrices[3]->price->decimal);
    }
}
