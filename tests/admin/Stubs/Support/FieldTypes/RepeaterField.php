<?php

namespace Lunar\Tests\Admin\Stubs\Support\FieldTypes;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Lunar\Admin\Support\FieldTypes\BaseFieldType;

class RepeaterField extends BaseFieldType
{
    public static function getFilamentComponent(\Lunar\Models\Attribute $attribute): Component
    {
        return Repeater::make($attribute->handle)
            ->schema([
                TextInput::make('label')->required(),
                TextInput::make('value')->required(),
            ])
            ->default([])
            ->collapsed();
    }
}
