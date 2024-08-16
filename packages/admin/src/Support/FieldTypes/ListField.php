<?php

namespace Lunar\Admin\Support\FieldTypes;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\KeyValue;
use Lunar\Admin\Support\Synthesizers\ListSynth;
use Lunar\Models\Attribute;

class ListField extends BaseFieldType
{
    protected static string $synthesizer = ListSynth::class;

    public static function getFilamentComponent(Attribute $attribute): Component
    {
        return KeyValue::make($attribute->handle)
            ->live()
            ->reorderable()
            ->dehydrateStateUsing(function ($state) {
                return $state;
            })
            ->when(filled($attribute->validation_rules), fn (KeyValue $component) => $component->rules($attribute->validation_rules))
            ->required((bool) $attribute->required)
            ->helperText($attribute->translate('description'));
    }
}
