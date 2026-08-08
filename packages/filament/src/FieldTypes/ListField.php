<?php

namespace Lunar\Filament\FieldTypes;

use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Component;
use Lunar\Core\Models\Attribute;
use Lunar\Filament\Synthesizers\ListSynth;

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
            ->required((bool) $attribute->required)
            ->helperText(null);
    }
}
