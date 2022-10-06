<?php

namespace Lunar\Tests\Unit\Rules;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Lunar\Rules\MaxDecimalPlaces;
use Lunar\Tests\TestCase;

/**
 * @group rules
 */
class MaxDecimalPlacesRuleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_validate_decimal_places_using_defaults()
    {
        $validator = Validator::make([
            'decimal' => 0.1,
        ], ['decimal' => new MaxDecimalPlaces()]);

        $this->assertTrue($validator->passes());

        $validator = Validator::make([
            'decimal' => 0.12,
        ], ['decimal' => new MaxDecimalPlaces()]);

        $this->assertTrue($validator->passes());

        $validator = Validator::make([
            'decimal' => 0.123,
        ], ['decimal' => new MaxDecimalPlaces()]);

        $this->assertTrue($validator->fails());
    }

    /** @test */
    public function can_validate_number_with_trailing_zeros()
    {
        $validator = Validator::make([
            'decimal' => '164.6400',
        ], ['decimal' => new MaxDecimalPlaces(2)]);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function can_validate_long_number()
    {
        $validator = Validator::make([
            'decimal' => 2000000000000000000,
        ], ['decimal' => new MaxDecimalPlaces(2)]);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function can_validate_on_passed_max_decimals()
    {
        $validator = Validator::make([
            'decimal' => 0.1,
        ], ['decimal' => new MaxDecimalPlaces(3)]);

        $this->assertTrue($validator->passes());

        $validator = Validator::make([
            'decimal' => 0.12,
        ], ['decimal' => new MaxDecimalPlaces(3)]);

        $this->assertTrue($validator->passes());

        $validator = Validator::make([
            'decimal' => 0.123,
        ], ['decimal' => new MaxDecimalPlaces(3)]);

        $this->assertTrue($validator->passes());

        $validator = Validator::make([
            'decimal' => 0.1234,
        ], ['decimal' => new MaxDecimalPlaces(3)]);

        $this->assertTrue($validator->fails());
    }

    /** @test */
    public function rule_works_on_integers()
    {
        $validator = Validator::make([
            'decimal' => 1,
        ], ['decimal' => new MaxDecimalPlaces()]);

        $this->assertTrue($validator->passes());

        $validator = Validator::make([
            'decimal' => 1,
        ], ['decimal' => new MaxDecimalPlaces(1)]);

        $this->assertTrue($validator->passes());

        $validator = Validator::make([
            'decimal' => 1,
        ], ['decimal' => new MaxDecimalPlaces(2)]);

        $this->assertTrue($validator->passes());

        $validator = Validator::make([
            'decimal' => 1,
        ], ['decimal' => new MaxDecimalPlaces(3)]);

        $this->assertTrue($validator->passes());
    }
}
