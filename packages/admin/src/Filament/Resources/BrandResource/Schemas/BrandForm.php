<?php

namespace Lunar\Admin\Filament\Resources\BrandResource\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Lunar\Admin\Support\Concerns\CallsHooks;
use Lunar\Admin\Support\Forms\Components\Attributes;

class BrandForm
{
    use CallsHooks;

    public static function configure(Schema $schema): Schema
    {
        return self::callStaticLunarHook(
            'configureForm',
            $schema
                ->components([
                    Section::make()->schema(static::getMainComponents()),
                    static::getAttributeDataComponent(),
                ])
                ->columns(1),
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
            ->label(__('lunarpanel::brand.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    public static function getAttributeDataComponent(): Component
    {
        return Attributes::make();
    }
}
