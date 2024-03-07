<?php

namespace Lunar\Admin\Support\Synthesizers;

use Lunar\FieldTypes\Toggle;

class ToggleSynth extends AbstractFieldSynth
{
    public static $key = 'lunar_toggle_field';

    protected static $targetClass = Toggle::class;
}
