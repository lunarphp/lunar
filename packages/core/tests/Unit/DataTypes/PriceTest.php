<?php

namespace GetCandy\Tests\Unit\DataTypes;

use GetCandy\DataTypes\Price;
use GetCandy\Exceptions\InvalidDataTypeValueException;
use GetCandy\Models\Currency;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group getcandy.datatypes
 */
class PriceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_initiate_the_datatype()
    {
        $currency = Currency::factory()->create([
            'code'           => 'GBP',
            'decimal_places' => 2,
        ]);

        $dataType = new Price(1500, $currency, 1);

        $this->assertInstanceOf(Price::class, $dataType);

        $this->assertEquals(1500, $dataType->value);
        $this->assertEquals(15.00, $dataType->decimal);
        $this->assertEquals('£15.00', $dataType->formatted);
    }

    /** @test */
    public function can_handle_multiple_decimal_places()
    {
        $currency = Currency::factory()->create([
            'code'           => 'GBP',
            'decimal_places' => 3,
        ]);

        $dataType = new Price(1500, $currency, 1);

        $this->assertEquals(1500, $dataType->value);
        $this->assertEquals(1.500, $dataType->decimal);
        $this->assertEquals('£1.50', $dataType->formatted);

        $dataType = new Price(155, $currency, 1);

        $this->assertEquals(155, $dataType->value);
        $this->assertEquals(0.155, $dataType->decimal);
        $this->assertEquals('£0.16', $dataType->formatted);
    }

    /** @test */
    public function can_handle_decimals_being_passed()
    {
        $currency = Currency::factory()->create([
            'code'           => 'GBP',
            'decimal_places' => 2,
        ]);

        $this->expectException(InvalidDataTypeValueException::class);

        new Price(15.99, $currency, 1);
    }
}
