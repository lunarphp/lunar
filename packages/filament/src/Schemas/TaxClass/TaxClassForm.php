<?php

namespace Lunar\Filament\Schemas\TaxClass;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Lunar\Admin\Support\Concerns\CallsHooks;

class TaxClassForm
{
    use CallsHooks;

    public static function configure(Schema $schema): Schema
    {
        return self::callStaticLunarHook(
            'configureForm',
            $schema->components(static::getMainComponents()),
        );
    }

    public static function getMainComponents(): array
    {
        return [
            Section::make()->schema([
                static::getNameComponent(),
                static::getDefaultComponent(),
            ]),
        ];
    }

    public static function getNameComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('lunarpanel::taxclass.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    public static function getDefaultComponent(): Component
    {
        return Toggle::make('default')
            ->label(__('lunarpanel::taxzone.form.default.label'));
    }
}
