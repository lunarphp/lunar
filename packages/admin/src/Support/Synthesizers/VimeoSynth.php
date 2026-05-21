<?php

namespace Lunar\Admin\Support\Synthesizers;

use Lunar\Core\FieldTypes\Vimeo;

class VimeoSynth extends AbstractFieldSynth
{
    public static $key = 'lunar_vimeo_field';

    protected static $targetClass = Vimeo::class;
}
