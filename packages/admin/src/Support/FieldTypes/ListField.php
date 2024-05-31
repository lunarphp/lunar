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
            ->rules($attribute->validation_rules)
            ->helperText($attribute->translate('description'));
    }
}
