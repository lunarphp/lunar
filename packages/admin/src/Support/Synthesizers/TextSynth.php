<?php

namespace Lunar\Admin\Support\Synthesizers;

use Lunar\FieldTypes\Text;

class TextSynth extends AbstractFieldSynth
{
    public static $key = 'lunar_text_field';

    protected static $targetClass = Text::class;
}
