<?php

namespace Lunar\Filament\FieldTypes;

use Filament\Forms\Components\Toggle as ToggleInput;
use Filament\Schemas\Components\Component;
use Lunar\Filament\Synthesizers\ToggleSynth;
use Lunar\Core\Models\Attribute;

class Toggle extends BaseFieldType
{
    protected static string $synthesizer = ToggleSynth::class;

    public static function getFilamentComponent(Attribute $attribute): Component
    {
        return ToggleInput::make($attribute->handle)
            ->helperText(
                $attribute->translate('description')
            )
            ->default(false)
            ->when(filled($attribute->validation_rules), fn (Toggle $component) => $component->rules($attribute->validation_rules))
            ->rule('boolean')
            ->required((bool) $attribute->required);
    }
}
