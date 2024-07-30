<?php

namespace Lunar\Admin\Support\Synthesizers;

use Lunar\FieldTypes\YouTube;

class YouTubeSynth extends AbstractFieldSynth
{
    public static $key = 'lunar_youtube_field';

    protected static $targetClass = YouTube::class;
}
