<?php

namespace Lunar\Shipping\Filament\Resources\ShippingExclusionListResource\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Lunar\Filament\Support\Concerns\CallsHooks;

class ShippingExclusionListForm
{
    use CallsHooks;

    public static function configure(Schema $schema): Schema
    {
        return self::callStaticLunarHook(
            'configureForm',
            $schema->components([
                Section::make()->schema(static::getMainComponents()),
            ]),
        );
    }

    public static function getMainComponents(): array
    {
        return [
            static::getNameComponent(),
        ];
    }

    public static function getNameComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('lunarpanel.shipping::shippingexclusionlist.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }
}
