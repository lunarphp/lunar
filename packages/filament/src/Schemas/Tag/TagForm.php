<?php

namespace Lunar\Filament\Schemas\Tag;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Lunar\Admin\Support\Concerns\CallsHooks;

class TagForm
{
    use CallsHooks;

    public static function configure(Schema $schema): Schema
    {
        return self::callStaticLunarHook(
            'configureForm',
            $schema
                ->components([
                    Section::make()->schema(static::getMainComponents()),
                ])
                ->columns(1),
        );
    }

    public static function getMainComponents(): array
    {
        return [
            static::getValueComponent(),
        ];
    }

    public static function getValueComponent(): Component
    {
        return TextInput::make('value')
            ->label(__('lunarpanel::tag.form.value.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }
}
