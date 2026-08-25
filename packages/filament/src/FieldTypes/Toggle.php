<?php

namespace Lunar\Filament\FieldTypes;

use Filament\Forms\Components\Toggle as ToggleInput;
use Filament\Schemas\Components\Component;
use Lunar\Core\Models\Attribute;
use Lunar\Filament\Synthesizers\ToggleSynth;

class Toggle extends BaseFieldType
{
    protected static string $synthesizer = ToggleSynth::class;

    public static function getFilamentComponent(Attribute $attribute): Component
    {
        return ToggleInput::make($attribute->handle)
            ->helperText(
                null
            )
            ->default(false)
            ->rules($attribute->validation_rules ?? [])
            ->rule('boolean')
            ->required((bool) $attribute->required);
    }
}
