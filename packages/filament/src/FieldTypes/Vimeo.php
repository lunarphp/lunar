<?php

namespace Lunar\Filament\FieldTypes;

use Filament\Schemas\Components\Component;
use Lunar\Core\Models\Attribute;
use Lunar\Filament\Forms\Components\Vimeo as VimeoInput;
use Lunar\Filament\Synthesizers\VimeoSynth;

class Vimeo extends BaseFieldType
{
    protected static string $synthesizer = VimeoSynth::class;

    public static function getFilamentComponent(Attribute $attribute): Component
    {
        return VimeoInput::make($attribute->handle)
            ->live(debounce: 200)
            ->rules($attribute->validation_rules ?? [])
            ->required((bool) $attribute->required)
            ->helperText(
                null ?? __('lunar-filament::components.forms.youtube.helperText')
            );
    }
}
