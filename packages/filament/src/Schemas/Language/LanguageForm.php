<?php

namespace Lunar\Filament\Schemas\Language;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Lunar\Admin\Support\Concerns\CallsHooks;

class LanguageForm
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
            static::getCodeComponent(),
            static::getDefaultComponent(),
        ];
    }

    public static function getNameComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('lunarpanel::language.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    public static function getCodeComponent(): Component
    {
        return TextInput::make('code')
            ->label(__('lunarpanel::language.form.code.label'))
            ->required()
            ->minLength(2)
            ->maxLength(5);
    }

    public static function getDefaultComponent(): Component
    {
        return Toggle::make('default')
            ->label(__('lunarpanel::language.form.default.label'));
    }
}
