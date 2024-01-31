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
        return KeyValue::make($attribute->handle)->dehydrateStateUsing(function ($state) {
            return $state;
        })->helperText($attribute->translate('description'));
    }
}
