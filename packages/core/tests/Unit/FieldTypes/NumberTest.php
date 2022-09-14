<?php

namespace Lunar\Tests\Unit\FieldTypes;

use Lunar\Exceptions\FieldTypeException;
use Lunar\FieldTypes\Number;
use Lunar\Tests\TestCase;

class NumberTest extends TestCase
{
    /** @test */
    public function can_set_value()
    {
        $field = new Number();
        $field->setValue(12345);

        $this->assertEquals(12345, $field->getValue());
    }

    /** @test */
    public function can_set_value_in_constructor()
    {
        $field = new Number(12345);

        $this->assertEquals(12345, $field->getValue());
    }

    /** @test */
    public function can_set_as_empty_string()
    {
        $field = new Number('');

        $this->assertEquals('', $field->getValue());

        $field->setValue('');

        $this->assertEquals('', $field->getValue());
    }

    /** @test */
    public function can_set_as_null_value()
    {
        $field = new Number(null);

        $this->assertEquals(null, $field->getValue());

        $field->setValue(null);

        $this->assertEquals(null, $field->getValue());
    }

    /** @test */
    public function check_does_not_allow_non_numerics()
    {
        $this->expectException(FieldTypeException::class);

        $field = new Number('bad string value');
    }
}
