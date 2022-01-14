<?php

namespace GetCandy\Tests\Unit\FieldTypes;

use GetCandy\Exceptions\FieldTypeException;
use GetCandy\FieldTypes\ListField;
use GetCandy\Tests\TestCase;

/**
 * @group core.fieldtypes
 */
class ListFieldTest extends TestCase
{
    /** @test */
    public function can_set_value()
    {
        $field = new ListField();
        $field->setValue([
            'Foo',
        ]);

        $this->assertEquals(['Foo'], $field->getValue());
    }

    /** @test */
    public function can_set_value_in_constructor()
    {
        $field = new ListField([
            'Foo',
        ]);

        $this->assertEquals(['Foo'], $field->getValue());
    }

    /** @test */
    public function check_does_not_allow_non_arrays()
    {
        $this->expectException(FieldTypeException::class);

        new ListField('Not an array');
    }
}
