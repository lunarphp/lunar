<?php

namespace Lunar\Filament\FieldTypes;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Lunar\Core\Models\Attribute;
use Lunar\Filament\Synthesizers\TextSynth;

class TextField extends BaseFieldType
{
    protected static string $synthesizer = TextSynth::class;

    public static function getFilamentComponent(Attribute $attribute): Component
    {
        if ($attribute->configuration->get('richtext')) {
            return RichEditor::make($attribute->handle)
                ->rules($attribute->validation_rules ?? [])
                ->required((bool) $attribute->required)
                ->helperText(null);
        }

        return TextInput::make($attribute->handle)
            ->rules($attribute->validation_rules ?? [])
            ->required((bool) $attribute->required)
            ->helperText(null);
    }
}
