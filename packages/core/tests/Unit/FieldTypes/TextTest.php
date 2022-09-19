<?php

namespace Lunar\Tests\Unit\FieldTypes;

use Lunar\Exceptions\FieldTypeException;
use Lunar\FieldTypes\Text;
use Lunar\Tests\TestCase;

/**
 * @group fieldtypes
 */
class TextTest extends TestCase
{
    /** @test */
    public function can_set_value()
    {
        $field = new Text();
        $field->setValue('I like cake');

        $this->assertEquals('I like cake', $field->getValue());

        $field = new Text();
        $field->setValue(12345);

        $this->assertEquals(12345, $field->getValue());

        $field = new Text();
        $field->setValue(true);

        $this->assertEquals(true, $field->getValue());
    }

    /** @test */
    public function can_set_null_value()
    {
        $field = new Text();
        $field->setValue(null);

        $this->assertEquals(null, $field->getValue());
    }

    /** @test */
    public function can_set_value_in_constructor()
    {
        $field = new Text('I like cake');

        $this->assertEquals('I like cake', $field->getValue());
    }

    /** @test */
    public function check_does_not_allow_non_strings()
    {
        $this->expectException(FieldTypeException::class);

        $field = new Text(new \stdClass());
    }
}
