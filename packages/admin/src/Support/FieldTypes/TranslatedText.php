<?php

namespace Lunar\Admin\Support\FieldTypes;

use Filament\Forms\Components\Component;
use Lunar\Admin\Support\Forms\Components\TranslatedText as TranslatedTextInput;
use Lunar\Admin\Support\Synthesizers\TranslatedTextSynth;
use Lunar\Models\Attribute;

class TranslatedText extends BaseFieldType
{
    protected static string $synthesizer = TranslatedTextSynth::class;

    public static function getConfigurationFields(): array
    {
        return TextField::getConfigurationFields();
    }

    public static function getFilamentComponent(Attribute $attribute): Component
    {
        return TranslatedTextInput::make($attribute->handle);
    }
}
