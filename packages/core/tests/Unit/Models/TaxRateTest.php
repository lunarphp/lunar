<?php

namespace GetCandy\Tests\Unit\Models;

use GetCandy\Models\Country;
use GetCandy\Models\CustomerGroup;
use GetCandy\Models\State;
use GetCandy\Models\TaxClass;
use GetCandy\Models\TaxRate;
use GetCandy\Models\TaxRateAmount;
use GetCandy\Models\TaxZone;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group getcandy.models
 */
class TaxRateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_a_tax_rate()
    {
        $data = [
            'name' => 'VAT',
            'tax_zone_id' => TaxZone::factory()->create()->id,
        ];

        $rate = TaxRate::factory()->create($data);

        $this->assertDatabaseHas((new TaxRate)->getTable(), $data);

        $this->assertInstanceOf(TaxZone::class, $rate->taxZone);
    }

    /** @test */
    public function tax_rate_can_have_amounts()
    {
        $data = [
            'name' => 'VAT',
            'tax_zone_id' => TaxZone::factory()->create()->id,
        ];

        $rate = TaxRate::factory()->create($data);

        $this->assertDatabaseHas((new TaxRate)->getTable(), $data);

        $this->assertCount(0, $rate->taxRateAmounts);

        $rate->taxRateAmounts()->create(TaxRateAmount::factory()->make()->toArray());

        $this->assertCount(1, $rate->refresh()->taxRateAmounts);
    }
}
