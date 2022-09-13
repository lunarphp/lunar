<?php

namespace Lunar\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\TaxRate;
use Lunar\Models\TaxRateAmount;
use Lunar\Models\TaxZone;
use Lunar\Tests\TestCase;

/**
 * @group lunar.models
 */
class TaxRateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_a_tax_rate()
    {
        $data = [
            'name'        => 'VAT',
            'tax_zone_id' => TaxZone::factory()->create()->id,
        ];

        $rate = TaxRate::factory()->create($data);

        $this->assertDatabaseHas((new TaxRate())->getTable(), $data);

        $this->assertInstanceOf(TaxZone::class, $rate->taxZone);
    }

    /** @test */
    public function tax_rate_can_have_amounts()
    {
        $data = [
            'name'        => 'VAT',
            'tax_zone_id' => TaxZone::factory()->create()->id,
        ];

        $rate = TaxRate::factory()->create($data);

        $this->assertDatabaseHas((new TaxRate())->getTable(), $data);

        $this->assertCount(0, $rate->taxRateAmounts);

        $rate->taxRateAmounts()->create(TaxRateAmount::factory()->make()->toArray());

        $this->assertCount(1, $rate->refresh()->taxRateAmounts);
    }
}
