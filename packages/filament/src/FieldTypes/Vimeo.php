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
            ->when(filled($attribute->validation_rules), fn (VimeoInput $component) => $component->rules($attribute->validation_rules))
            ->required((bool) $attribute->required)
            ->helperText(
                $attribute->translate('description') ?? __('lunar-filament::components.forms.youtube.helperText')
            );
    }
}
