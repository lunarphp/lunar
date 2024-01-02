<?php

namespace Lunar\Shipping\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Shipping\Filament\Resources\ShippingZoneResource\Pages;
use Lunar\Shipping\Models\ShippingZone;

class ShippingZoneResource extends BaseResource
{
    //    protected static ?string $permission = 'settings:shipping';

    protected static ?string $model = ShippingZone::class;

    protected static ?int $navigationSort = 1;

    public static function getLabel(): string
    {
        return 'Shipping Zone';
    }

    public static function getPluralLabel(): string
    {
        return 'Shipping Zones';
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::tax');
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Shipping';
    }

    protected static function getMainFormComponents(): array
    {
        return [
            static::getNameFormComponent(),
            static::getTypeFormComponent(),
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

    protected static function getTypeFormComponent(): Component
    {
        return Forms\Components\Select::make('type')
            ->label('Type')
            ->required()
            ->options([
                'unrestricted' => 'Unrestricted',
                'countries' => 'Limit to Countries',
                'states' => 'Limit to States / Provinces',
                'postcodes' => 'Limit to Postcodes',
            ]);
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
            Tables\Columns\TextColumn::make('name')
                ->label('Name'),
            Tables\Columns\TextColumn::make('type')
                ->label('Type'),
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
            'index' => Pages\ListShippingZones::route('/'),
        ];
    }
}
