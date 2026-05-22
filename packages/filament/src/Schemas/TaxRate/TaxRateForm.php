<?php

namespace Lunar\Filament\Schemas\TaxRate;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Lunar\Admin\Support\Concerns\CallsHooks;

class TaxRateForm
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
                static::getPriorityComponent(),
                static::getTaxZoneComponent(),
            ]),
        ];
    }

    public static function getNameComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('lunarpanel::taxrate.form.name.label'))
            ->unique(column: 'name', ignoreRecord: true)
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    public static function getPriorityComponent(): Component
    {
        return TextInput::make('priority')
            ->label(__('lunarpanel::taxrate.form.priority.label'))
            ->required()
            ->numeric()
            ->maxLength(255)
            ->autofocus();
    }

    public static function getTaxZoneComponent(): Component
    {
        return Select::make('tax_zone_id')
            ->relationship(name: 'taxZone', titleAttribute: 'name')
            ->label(__('lunarpanel::taxrate.form.tax_zone_id.label'))
            ->live()
            ->required();
    }
}
