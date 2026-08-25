<?php

namespace Lunar\Filament\FieldTypes;

use Filament\Schemas\Components\Component;
use Lunar\Core\Models\Attribute;
use Lunar\Filament\Forms\Components\YouTube as YouTubeInput;
use Lunar\Filament\Synthesizers\YouTubeSynth;

class YouTube extends BaseFieldType
{
    protected static string $synthesizer = YouTubeSynth::class;

    public static function getFilamentComponent(Attribute $attribute): Component
    {
        return YouTubeInput::make($attribute->handle)
            ->live(debounce: 200)
            ->rules($attribute->validation_rules ?? [])
            ->required((bool) $attribute->required)
            ->helperText(
                null ?? __('lunar-filament::components.forms.youtube.helperText')
            );
    }
}
