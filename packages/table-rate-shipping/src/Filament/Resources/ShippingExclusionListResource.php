<?php

namespace Lunar\Shipping\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Shipping\Filament\Resources\ShippingExclusionListResource\Pages;
use Lunar\Shipping\Filament\Resources\ShippingExclusionListResource\RelationManagers\ShippingExclusionRelationManager;
use Lunar\Shipping\Models\ShippingExclusionList;

class ShippingExclusionListResource extends BaseResource
{
    protected static ?string $model = ShippingExclusionList::class;

    protected static ?int $navigationSort = 1;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;

    public static function getLabel(): string
    {
        return 'Shipping Exclusion List';
    }

    public static function getPluralLabel(): string
    {
        return 'Shipping Exclusion Lists';
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::shipping-exclusion-lists');
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Shipping';
    }

    public static function getDefaultForm(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema(
                static::getMainFormComponents(),
            ),
        ]);
    }

    protected static function getDefaultRelations(): array
    {
        return [
            ShippingExclusionRelationManager::class,
        ];
    }

    protected static function getMainFormComponents(): array
    {
        return [
            static::getNameFormComponent(),
        ];
    }

    public static function getNameFormComponent(): Component
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('lunarpanel::taxzone.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
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

    public static function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label('Name'),
            Tables\Columns\TextColumn::make('exclusions_count')->counts('exclusions'),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShippingExclusionLists::route('/'),
            'edit' => Pages\EditShippingExclusionList::route('/{record}/edit'),
            //            'rates' => Pages\ManageShippingRates::route('/{record}/rates'),
        ];
    }
}
