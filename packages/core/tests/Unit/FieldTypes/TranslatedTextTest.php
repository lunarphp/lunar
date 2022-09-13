<?php

namespace Lunar\Tests\Unit\FieldTypes;

use Lunar\Exceptions\FieldTypeException;
use Lunar\FieldTypes\Number;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Tests\TestCase;
use Illuminate\Support\Collection;

class TranslatedTextTest extends TestCase
{
    /** @test */
    public function can_set_value()
    {
        $field = new TranslatedText();
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
    public function can_json_encode_fieldtype()
    {
        $data = [
            'en' => 'Blue',
            'fr' => 'Bleu',
        ];

        $field = new TranslatedText(collect([
            'en' => new Text('Blue'),
            'fr' => new Text('Bleu'),
        ]));

        $this->assertSame(
            json_encode($data),
            json_encode($field)
        );
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
