<?php

namespace Lunar\Filament\FieldTypes;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Lunar\Filament\Synthesizers\TextSynth;
use Lunar\Core\Models\Attribute;

class TextField extends BaseFieldType
{
    protected static string $synthesizer = TextSynth::class;

    public static function getConfigurationFields(): array
    {
        return [
            Toggle::make('richtext')->label(
                __('lunar-filament::fieldtypes.text.form.richtext.label')
            ),
        ];
    }

    public static function getFilamentComponent(Attribute $attribute): Component
    {
        if ($attribute->configuration->get('richtext')) {
            return RichEditor::make($attribute->handle)
                ->when(filled($attribute->validation_rules), fn (RichEditor $component) => $component->rules($attribute->validation_rules))
                ->required((bool) $attribute->required)
                ->helperText($attribute->translate('description'));
        }

        return TextInput::make($attribute->handle)
            ->when(filled($attribute->validation_rules), fn (TextInput $component) => $component->rules($attribute->validation_rules))
            ->required((bool) $attribute->required)
            ->helperText($attribute->translate('description'));
    }
}
