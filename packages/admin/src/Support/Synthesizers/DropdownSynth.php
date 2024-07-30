<?php

namespace Lunar\Admin\Support\Synthesizers;

use Lunar\FieldTypes\Dropdown;

class DropdownSynth extends AbstractFieldSynth
{
    public static $key = 'lunar_dropdown_field';

    protected static $targetClass = Dropdown::class;
}
