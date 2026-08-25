<?php

namespace Lunar\Filament\FieldTypes;

use Filament\Schemas\Components\Component;
use Lunar\Core\Models\Attribute;
use Lunar\Filament\Forms\Components\TranslatedText as TranslatedTextComponent;
use Lunar\Filament\Synthesizers\TranslatedTextSynth;

class TranslatedText extends BaseFieldType
{
    protected static string $synthesizer = TranslatedTextSynth::class;

    public static function getFilamentComponent(Attribute $attribute): Component
    {
        return TranslatedTextComponent::make($attribute->handle)
            ->optionRichtext((bool) $attribute->configuration->get('richtext'))
            ->rules($attribute->validation_rules ?? [])
            ->required((bool) $attribute->required)
            ->helperText(null);
    }
}
