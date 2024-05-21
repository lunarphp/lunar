<?php

namespace Lunar\Admin\Support\Synthesizers;

use Lunar\FieldTypes\Textarea;

class TextareaSynth extends AbstractFieldSynth
{
    public static $key = 'lunar_textarea_field';

    protected static $targetClass = Textarea::class;
}
