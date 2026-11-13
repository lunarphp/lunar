<?php

namespace Lunar\Filament\Schemas\Location;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Lunar\Filament\Forms\Components\CountrySelect;
use Lunar\Filament\Forms\Components\StateSelect;
use Lunar\Filament\Support\Concerns\CallsHooks;

class LocationForm
{
    use CallsHooks;

    public static function configure(Schema $schema): Schema
    {
        return self::callStaticLunarHook(
            'configureForm',
            $schema->components([
                Section::make()->schema(static::getMainComponents()),
                Section::make(__('lunar-filament::location.form.address.label'))
                    ->schema(static::getAddressComponents())
                    ->columns(2),
            ]),
        );
    }

    /**
     * @return array<int, Component>
     */
    public static function getMainComponents(): array
    {
        return [
            static::getNameComponent(),
            static::getHandleComponent(),
            static::getDefaultComponent(),
        ];
    }

    /**
     * Where the location is, so a pickup can tell the customer where to go.
     *
     * @return array<int, Component>
     */
    public static function getAddressComponents(): array
    {
        $text = fn (string $field): Component => TextInput::make($field)
            ->label(__("lunar-filament::address.form.{$field}.label"))
            ->maxLength(255);

        return [
            $text('line_one'),
            $text('line_two'),
            $text('line_three'),
            $text('city'),
            CountrySelect::make('country_id')->label(__('lunar-filament::address.form.country_id.label')),
            StateSelect::make('state')->label(__('lunar-filament::address.form.state.label'))->dependsOn('country_id'),
            $text('postcode'),
            $text('contact_phone'),
        ];
    }

    public static function getNameComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('lunar-filament::location.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    public static function getHandleComponent(): Component
    {
        return TextInput::make('handle')
            ->label(__('lunar-filament::location.form.handle.label'))
            ->required()
            ->unique(ignoreRecord: true)
            ->live(onBlur: true)
            ->afterStateUpdated(function (string $operation, $state, Set $set) {
                if ($operation !== 'create') {
                    return;
                }

                $set('handle', Str::slug($state));
            })
            ->minLength(2)
            ->maxLength(255);
    }

    public static function getDefaultComponent(): Component
    {
        return Toggle::make('default')
            ->label(__('lunar-filament::location.form.default.label'));
    }
}
