<?php

namespace Lunar\Admin\Support\FieldTypes;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\Textarea;
use Lunar\Admin\Support\Synthesizers\TextareaSynth;
use Lunar\Models\Attribute;

class TextareaField extends BaseFieldType
{
    protected static string $synthesizer = TextareaSynth::class;

    public static function getConfigurationFields(): array
    {
        return [
            \Filament\Forms\Components\Toggle::make('autosize')->label(
                __('lunarpanel::fieldtypes.textarea.form.autosize.label')
            ),
        ];
    }

    public static function getFilamentComponent(Attribute $attribute): Component
    {
        return Textarea::make($attribute->handle)
            ->helperText($attribute->translate('description'))
            ->autosize($attribute->configuration->get('autosize'));
    }
}
