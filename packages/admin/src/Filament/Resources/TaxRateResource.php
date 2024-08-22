<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Clusters\Taxes;
use Lunar\Admin\Filament\Resources\TaxRateResource\Pages;
use Lunar\Admin\Filament\Resources\TaxRateResource\RelationManagers\TaxRateAmountRelationManager;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Models\TaxRate;

class TaxRateResource extends BaseResource
{
    protected static ?string $cluster = Taxes::class;

    protected static ?string $permission = 'settings:core';

    protected static ?string $model = TaxRate::class;

    protected static ?int $navigationSort = 1;

    public static function getLabel(): string
    {
        return __('lunarpanel::taxrate.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::taxrate.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::tax');
    }

    protected static function getMainFormComponents(): array
    {
        return [
            Forms\Components\Section::make()->schema([
                static::getNameFormComponent(),
                static::getPriorityFormComponent(),
                static::getTaxZoneFormComponent(),
            ]),
        ];
    }

    public static function getNameFormComponent(): Component
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('lunarpanel::taxrate.form.name.label'))
            ->unique(column: 'name')
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    public static function getPriorityFormComponent(): Component
    {
        return Forms\Components\TextInput::make('priority')
            ->label(__('lunarpanel::taxrate.form.priority.label'))
            ->required()
            ->numeric()
            ->maxLength(255)
            ->autofocus();
    }

    public static function getTaxZoneFormComponent(): Component
    {
        return Forms\Components\Select::make('tax_zone_id')
            ->relationship(name: 'taxZone', titleAttribute: 'name')
            ->label(__('lunarpanel::taxrate.form.tax_zone_id.label'))
            ->live()
            ->required();
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
            Tables\Columns\TextColumn::make('name'),
            Tables\Columns\TextColumn::make('taxZone.name')
                ->label(__('lunarpanel::taxrate.table.tax_zone.label')),
            Tables\Columns\TextColumn::make('priority')
                ->label(__('lunarpanel::taxrate.table.priority.label')),
        ];
    }

    public static function getRelations(): array
    {
        return [
            TaxRateAmountRelationManager::class,
        ];
    }

    public static function getDefaultPages(): array
    {
        return [
            'index' => Pages\ListTaxRates::route('/'),
            'edit' => Pages\EditTaxRate::route('/{record}/edit'),
            'create' => Pages\CreateTaxRate::route('/create'),
        ];
    }
}
