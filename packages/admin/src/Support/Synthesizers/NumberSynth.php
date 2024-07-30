<?php

namespace Lunar\Admin\Support\Synthesizers;

use Lunar\FieldTypes\Number;

class NumberSynth extends AbstractFieldSynth
{
    public static $key = 'lunar_number_field';

    protected static $targetClass = Number::class;
}
