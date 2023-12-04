<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\TaxZoneResource\Pages;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Models\TaxZone;

class TaxZoneResource extends BaseResource
{
    protected static ?string $permission = 'settings:core';

    protected static ?string $model = TaxZone::class;

    protected static ?int $navigationSort = 1;

    public static function getLabel(): string
    {
        return __('lunarpanel::taxzone.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::taxzone.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::tax');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.settings');
    }

    protected static function getMainFormComponents(): array
    {
        return [
            static::getNameFormComponent(),
            static::getZoneTypeFormComponent(),
            static::getPriceDisplayFormComponent(),
            static::getActiveFormComponent(),
            static::getDefaultFormComponent(),
        ];
    }

    protected static function getNameFormComponent(): Component
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('lunarpanel::taxzone.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getZoneTypeFormComponent(): Component
    {
        return Forms\Components\Select::make('zone_type')
            ->options([
                'country' => __('lunarpanel::taxzone.form.zone_type.options.country'),
                'states' => __('lunarpanel::taxzone.form.zone_type.options.states'),
                'postcodes' => __('lunarpanel::taxzone.form.zone_type.options.postcodes'),
            ])
            ->label(__('lunarpanel::taxzone.form.zone_type.label'))
            ->required();
    }

    protected static function getPriceDisplayFormComponent(): Component
    {
        return Forms\Components\Select::make('price_display')
            ->options([
                'include_tax' => __('lunarpanel::taxzone.form.price_display.options.include_tax'),
                'exclude_tax' => __('lunarpanel::taxzone.form.price_display.options.exclude_tax'),
            ])
            ->label(__('lunarpanel::taxzone.form.price_display.label'))
            ->required();
    }

    protected static function getActiveFormComponent(): Component
    {
        return Forms\Components\Toggle::make('active')
            ->label(__('lunarpanel::taxzone.form.active.label'));
    }

    protected static function getDefaultFormComponent(): Component
    {
        return Forms\Components\Toggle::make('default')
            ->label(__('lunarpanel::taxzone.form.default.label'));
    }

    public static function getDefaultTable(Table $table): Table
    {
        return $table
            ->columns(static::getTableColumns())
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function getTableColumns(): array
    {
        return [
            Tables\Columns\BooleanColumn::make('default')
                ->label(__('lunarpanel::taxzone.table.default.label')),
            Tables\Columns\TextColumn::make('name')
                ->label(__('lunarpanel::taxzone.table.name.label')),
            Tables\Columns\TextColumn::make('zone_type')
                ->label(__('lunarpanel::taxzone.table.zone_type.label')),
            Tables\Columns\BooleanColumn::make('active')
                ->label(__('lunarpanel::taxzone.table.active.label')),
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTaxZones::route('/'),
            'create' => Pages\CreateTaxZone::route('/create'),
            'edit' => Pages\EditTaxZone::route('/{record}/edit'),
        ];
    }
}
