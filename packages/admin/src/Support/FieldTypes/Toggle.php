<?php

namespace Lunar\Admin\Support\FieldTypes;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\Toggle as ToggleInput;
use Lunar\Admin\Support\Synthesizers\ToggleSynth;
use Lunar\Models\Attribute;

class Toggle extends BaseFieldType
{
    protected static string $synthesizer = ToggleSynth::class;

    public static function getFilamentComponent(Attribute $attribute): Component
    {
        return ToggleInput::make($attribute->handle)->default('true')->live();
    }
}
