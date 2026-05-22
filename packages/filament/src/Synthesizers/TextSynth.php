<?php

namespace Lunar\Filament\Synthesizers;

use Lunar\Core\FieldTypes\Text;

class TextSynth extends AbstractFieldSynth
{
    public static $key = 'lunar_text_field';

    protected static $targetClass = Text::class;
}
