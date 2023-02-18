<?php

namespace Lunar\Tests\Unit\Jobs\Prices;

use Illuminate\Bus\PendingBatch;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Lunar\Jobs\Prices\ConvertPrices;
use Lunar\Jobs\Prices\DispatchPriceConversionOnCurrencyUpdate;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Tests\TestCase;

/**
 * @group lunar.jobs.prices
 */
class DispatchPriceConversionOnCurrencyUpdateTest extends TestCase
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
    public function dispatches_price_conversion_job_batch_on_currency_update()
    {
        Bus::fake([ConvertPrices::class]);

        $currencies = Currency::factory()
            ->count(2)
            ->sequence(
                ['default' => true],
                ['default' => false],
            )
            ->create();

        $purchasable = ProductVariant::factory()->create();

        Price::factory()
            ->count(2)
            ->sequence(fn(Sequence $sequence) => [
                'currency_id' => $currencies[$sequence->index % 2]->id,
            ])
            ->create([
                'price' => 100,
                'priceable_type' => $purchasable->getMorphClass(),
                'priceable_id' => $purchasable->id,
            ]);

        DispatchPriceConversionOnCurrencyUpdate::dispatchSync($currencies[1]);

        Bus::assertBatched(function (PendingBatch $batch) {
            return in_array('Price Conversion', $batch->options['tags'], true);
        });
    }

    /** @test */
    public function can_correctly_convert_prices_on_default_currency_update()
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
                    'exchange_rate' => 1.347,
                ],
                2 => [
                    'default' => false,
                    'decimal_places' => 3,
                    'exchange_rate' => 3.184,
                ],
                3 => [
                    'default' => false,
                    'decimal_places' => 4,
                    'exchange_rate' => 0.886,
                ],
            })
            ->create();

        $purchasables = ProductVariant::factory()
            ->count(2)
            ->create();

        $prices = Price::factory()
            ->count(8)
            ->sequence(fn(Sequence $sequence) => [
                'currency_id' => $currencies[$sequence->index % 4]->id,
                'priceable_id' => $purchasables[intdiv($sequence->index, 4)]->id,
                'price' => intdiv($sequence->index, 4) === 0 ? 13788 : 6695,
            ])
            ->create([
                'priceable_type' => $purchasables[0]->getMorphClass(),
            ]);

        DispatchPriceConversionOnCurrencyUpdate::dispatchSync($currencies[1]);

        $updatedPrices = $prices->fresh();

        self::assertEquals(185.72, $updatedPrices[1]->price->decimal);
        self::assertEquals(90.18, $updatedPrices[5]->price->decimal);

        DispatchPriceConversionOnCurrencyUpdate::dispatchSync($currencies[2]);

        $updatedPrices = $prices->fresh();

        self::assertEquals(439.009, $updatedPrices[2]->price->decimal);
        self::assertEquals(213.168, $updatedPrices[6]->price->decimal);

        DispatchPriceConversionOnCurrencyUpdate::dispatchSync($currencies[3]);

        $updatedPrices = $prices->fresh();

        self::assertEquals(122.1616, $updatedPrices[3]->price->decimal);
        self::assertEquals(59.3177, $updatedPrices[7]->price->decimal);
    }
}
