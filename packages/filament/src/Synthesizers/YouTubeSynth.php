<?php

namespace Lunar\Filament\Synthesizers;

use Lunar\Core\FieldTypes\YouTube;

class YouTubeSynth extends AbstractFieldSynth
{
    public static $key = 'lunar_youtube_field';

    protected static $targetClass = YouTube::class;
}
