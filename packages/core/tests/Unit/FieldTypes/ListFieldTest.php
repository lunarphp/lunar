<?php

namespace Lunar\Tests\Unit\FieldTypes;

use Lunar\Exceptions\FieldTypeException;
use Lunar\FieldTypes\ListField;
use Lunar\Tests\TestCase;

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
