<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Clusters\Taxes;
use Lunar\Admin\Filament\Resources\TaxRateResource\Pages\CreateTaxRate;
use Lunar\Admin\Filament\Resources\TaxRateResource\Pages\EditTaxRate;
use Lunar\Admin\Filament\Resources\TaxRateResource\Pages\ListTaxRates;
use Lunar\Admin\Filament\Resources\TaxRateResource\RelationManagers\TaxRateAmountRelationManager;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Models\Contracts\TaxRate as TaxRateContract;

class TaxRateResource extends BaseResource
{
    protected static ?string $cluster = Taxes::class;

    protected static ?string $permission = 'settings:core';

    protected static ?string $model = TaxRateContract::class;

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
            Section::make()->schema([
                static::getNameFormComponent(),
                static::getPriorityFormComponent(),
                static::getTaxZoneFormComponent(),
            ]),
        ];
    }

    public static function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('lunarpanel::taxrate.form.name.label'))
            ->unique(column: 'name', ignoreRecord: true)
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    public static function getPriorityFormComponent(): Component
    {
        return TextInput::make('priority')
            ->label(__('lunarpanel::taxrate.form.priority.label'))
            ->required()
            ->numeric()
            ->maxLength(255)
            ->autofocus();
    }

    public static function getTaxZoneFormComponent(): Component
    {
        return Select::make('tax_zone_id')
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
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function getTableColumns(): array
    {
        return [
            TextColumn::make('name'),
            TextColumn::make('taxZone.name')
                ->label(__('lunarpanel::taxrate.table.tax_zone.label')),
            TextColumn::make('priority')
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
            'index' => ListTaxRates::route('/'),
            'edit' => EditTaxRate::route('/{record}/edit'),
            'create' => CreateTaxRate::route('/create'),
        ];
    }
}
