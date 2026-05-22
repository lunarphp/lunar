<?php

namespace Lunar\Filament\Synthesizers;

use Lunar\Core\FieldTypes\Dropdown;

class DropdownSynth extends AbstractFieldSynth
{
    public static $key = 'lunar_dropdown_field';

    protected static $targetClass = Dropdown::class;
}
