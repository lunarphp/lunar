<?php

namespace Lunar\Tests\Unit\Base\Casts;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Base\Casts\TaxBreakdown;
use Lunar\Models\CartLine;
use Lunar\Models\Currency;
use Lunar\Tests\TestCase;

/**
 * @group model.casts
 */
class TaxBreakdownTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_serialize_from_array()
    {
        $breakDown = new TaxBreakdown;

        $result = $breakDown->serialize(
            new CartLine,
            'foo',
            collect([
                [
                    'total' => [
                        'value' => 123,
                        'formatted' => '£1.23',
                        'currency' => Currency::factory()->create()->toArray(),
                    ]
                ]
            ]),
            []
        );

        $this->assertJson($result);
        $json = json_decode($result);

        $this->assertCount(1, $json);
        $this->assertIsObject($json[0]);
    }
}
