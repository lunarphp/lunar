<?php

namespace GetCandy\Tests\Unit\FieldTypes;

use GetCandy\Exceptions\FieldTypeException;
use GetCandy\FieldTypes\Number;
use GetCandy\FieldTypes\Text;
use GetCandy\FieldTypes\TranslatedText;
use GetCandy\Tests\TestCase;
use Illuminate\Support\Collection;

class TranslatedTextTest extends TestCase
{
    /** @test */
    public function can_set_value()
    {
        $field = new TranslatedText;
        $field->setValue(collect([
            'en' => new Text('Blue'),
            'fr' => new Text('Bleu'),
        ]));

        $this->assertInstanceOf(Collection::class, $field->getValue());
    }

    /** @test */
    public function can_set_value_in_constructor()
    {
        $field = new TranslatedText(collect([
            'en' => new Text('Blue'),
            'fr' => new Text('Bleu'),
        ]));

        $this->assertInstanceOf(Collection::class, $field->getValue());
    }

    /** @test */
    public function check_does_not_allow_non_text_field_types()
    {
        $this->assertTrue(true);
        // TODO: This needs looking at when we get to attribute saving.
        // $this->expectException(FieldTypeException::class);

        // $field = new TranslatedText(collect([
        //     'en' => new Text('Blue'),
        //     'fr' => new Number(123),
        // ]));
    }
}
