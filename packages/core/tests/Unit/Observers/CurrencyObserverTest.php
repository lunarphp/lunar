<?php

namespace Lunar\Tests\Unit\Observers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Lunar\Jobs\Prices\DispatchPriceConversionOnCurrencyUpdate;
use Lunar\Models\Currency;
use Lunar\Tests\TestCase;

/**
 * @group observers
 */
class CurrencyObserverTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // price auto-conversion config
        Config::set([
            'lunar.pricing.auto_conversion.enabled' => true,
            'lunar.pricing.auto_conversion.currency_update_job' => DispatchPriceConversionOnCurrencyUpdate::class,
        ]);
    }

    /** @test */
    public function dispatches_auto_price_conversion_job_on_currency_update()
    {
        Bus::fake();

        $currency = Currency::factory()->create([
            'default' => true,
            'decimal_places' => 2,
            'exchange_rate' => 1.1
        ]);

        Bus::assertNotDispatched(DispatchPriceConversionOnCurrencyUpdate::class);

        $currency->update([
            'exchange_rate' => 1.2
        ]);

        Bus::assertNotDispatched(DispatchPriceConversionOnCurrencyUpdate::class);

        $currency->update([
            'default' => false,
            'decimal_places' => 1
        ]);

        Bus::assertNotDispatched(DispatchPriceConversionOnCurrencyUpdate::class);

        $currency->update([
            'exchange_rate' => 1.3
        ]);

        Bus::assertDispatched(function (DispatchPriceConversionOnCurrencyUpdate $job) use ($currency) {
            return $job->savedCurrency->id === $currency->id;
        });
    }
}
