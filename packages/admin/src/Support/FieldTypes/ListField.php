<?php

namespace Lunar\Admin\Support\FieldTypes;

use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Component;
use Lunar\Admin\Support\Synthesizers\ListSynth;
use Lunar\Models\Attribute;

class ListField extends BaseFieldType
{
    protected static string $synthesizer = ListSynth::class;

    public static function getFilamentComponent(Attribute $attribute): Component
    {
        return KeyValue::make($attribute->handle)
            ->reorderable()
            ->dehydrateStateUsing(function ($state) {
                return $state;
            })
            ->when(filled($attribute->validation_rules), fn (KeyValue $component) => $component->rules($attribute->validation_rules))
            ->required((bool) $attribute->required)
            ->helperText($attribute->translate('description'));
    }
}
