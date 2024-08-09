<?php

namespace Lunar\Admin\Support\FieldTypes;

use Filament\Forms\Components\Component;
use Lunar\Admin\Support\Forms\Components\TranslatedText as TranslatedTextComponent;
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
        return TranslatedTextComponent::make($attribute->handle)
            ->optionRichtext((bool) $attribute->configuration->get('richtext'))
            ->when(filled($attribute->validation_rules), fn (TranslatedTextComponent $component) => $component->rules($attribute->validation_rules))
            ->required((bool) $attribute->required)
            ->helperText($attribute->translate('description'));
    }
}
