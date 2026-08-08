<?php

namespace Lunar\Filament\FieldTypes;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Lunar\Core\Models\Attribute;
use Lunar\Filament\Synthesizers\NumberSynth;

class Number extends BaseFieldType
{
    protected static string $synthesizer = NumberSynth::class;

    public static function getFilamentComponent(Attribute $attribute): Component
    {
        $min = (int) $attribute->configuration->get('min');
        $max = (int) $attribute->configuration->get('max');

        $input = TextInput::make($attribute->handle)
            ->numeric()
            ->required((bool) $attribute->required)
            ->helperText(null);

        if ($min) {
            $input->minValue($min);
        }

        if ($max) {
            $input->maxValue($max);
        }

        return $input;
    }
}
