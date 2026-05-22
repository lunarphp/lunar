<?php

namespace Lunar\Filament\Synthesizers;

use Lunar\Core\FieldTypes\Vimeo;

class VimeoSynth extends AbstractFieldSynth
{
    public static $key = 'lunar_vimeo_field';

    protected static $targetClass = Vimeo::class;
}
