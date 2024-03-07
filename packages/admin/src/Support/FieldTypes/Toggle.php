<?php

namespace Lunar\Admin\Support\FieldTypes;

use Filament\Forms\Components\Component;
use Lunar\Admin\Support\Forms\Components\Toggle as ToggleInput;
use Lunar\Admin\Support\Synthesizers\ToggleSynth;
use Lunar\Models\Attribute;

class Toggle extends BaseFieldType
{
    protected static string $synthesizer = ToggleSynth::class;

    public static function getFilamentComponent(Attribute $attribute): Component
    {
        return ToggleInput::make($attribute->handle)->default('false')
            ->onIcon('heroicon-m-check')
            ->helperText(
                $attribute->translate('description')
            )
            ->live();
    }
}
