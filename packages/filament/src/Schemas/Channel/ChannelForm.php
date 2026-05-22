<?php

namespace Lunar\Filament\Schemas\Channel;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Lunar\Filament\Support\Concerns\CallsHooks;

class ChannelForm
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
            static::getHandleComponent(),
            static::getUrlComponent(),
            static::getDefaultComponent(),
        ];
    }

    public static function getNameComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('lunar-filament::channel.form.name.label'))
            ->required()
            ->maxLength(255)
            ->afterStateUpdated(function (string $operation, $state, Set $set) {
                if ($operation !== 'create') {
                    return;
                }
                $set('handle', Str::slug($state));
            })
            ->live(onBlur: true)
            ->autofocus();
    }

    public static function getHandleComponent(): Component
    {
        return TextInput::make('handle')
            ->label(__('lunar-filament::channel.form.handle.label'))
            ->required()
            ->unique(ignoreRecord: true)
            ->live(onBlur: true)
            ->afterStateUpdated(function (string $operation, $state, Set $set) {
                if ($operation !== 'create') {
                    return;
                }

                $set('handle', Str::snake(Str::lower($state)));
            })
            ->minLength(3)
            ->maxLength(255);
    }

    public static function getUrlComponent(): Component
    {
        return TextInput::make('url')
            ->label(__('lunar-filament::channel.form.url.label'))
            ->maxLength(255)
            ->autofocus();
    }

    public static function getDefaultComponent(): Component
    {
        return Toggle::make('default')
            ->label(__('lunar-filament::channel.form.default.label'));
    }
}
