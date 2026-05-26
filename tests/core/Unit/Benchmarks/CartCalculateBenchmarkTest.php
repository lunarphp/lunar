<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class)->group('bench');
uses(RefreshDatabase::class);

test('Cart::calculate on a 50-line cart', function () {
    $lineCount = 50;
    $warmup = 5;
    $samples = 50;

    Channel::factory()->create();

    $currency = Currency::factory()->create([
        'code' => 'GBP',
        'default' => true,
    ]);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    foreach (range(1, $lineCount) as $i) {
        $variant = ProductVariant::factory()->create();

        Price::factory()->create([
            'price' => 100 + $i,
            'min_quantity' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => $variant->getMorphClass(),
            'priceable_id' => $variant->id,
        ]);

        CartLine::factory()->create([
            'cart_id' => $cart->id,
            'purchasable_type' => $variant->getMorphClass(),
            'purchasable_id' => $variant->id,
            'quantity' => 1 + ($i % 5),
        ]);
    }

    for ($i = 0; $i < $warmup; $i++) {
        $cart->calculate(force: true);
    }

    $times = [];

    for ($i = 0; $i < $samples; $i++) {
        $t0 = hrtime(true);
        $cart->calculate(force: true);
        $times[] = (hrtime(true) - $t0) / 1_000_000.0;
    }

    $peakMem = memory_get_peak_usage();

    sort($times);
    $mean = array_sum($times) / count($times);
    $median = $times[(int) (count($times) / 2)];
    $min = $times[0];
    $max = end($times);

    fwrite(STDOUT, sprintf(
        "\n[bench] lines=%d samples=%d  min=%.3fms  median=%.3fms  mean=%.3fms  max=%.3fms  peakMem=%.2fMB\n",
        $lineCount,
        $samples,
        $min,
        $median,
        $mean,
        $max,
        $peakMem / 1024 / 1024,
    ));

    expect(true)->toBeTrue();
});
