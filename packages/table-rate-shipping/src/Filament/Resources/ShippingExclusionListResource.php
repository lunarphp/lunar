<?php

namespace Lunar\Shipping\Filament\Resources;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Shipping\Filament\Resources\ShippingExclusionListResource\Pages;
use Lunar\Shipping\Filament\Resources\ShippingExclusionListResource\RelationManagers\ShippingExclusionRelationManager;
use Lunar\Shipping\Models\Contracts\ShippingExclusionList;

class ShippingExclusionListResource extends BaseResource
{
    protected static ?string $model = ShippingExclusionList::class;

    protected static ?string $permission = 'shipping:manage';

    protected static ?int $navigationSort = 1;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;

    public static function getLabel(): string
    {
        return __('lunarpanel.shipping::shippingexclusionlist.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel.shipping::shippingexclusionlist.label_plural');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::shipping-exclusion-lists');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel.shipping::plugin.navigation.group');
    }

    public static function getDefaultForm(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema(
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
            ->label(__('lunarpanel.shipping::shippingexclusionlist.form.name.label'))
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
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label(
                    __('lunarpanel.shipping::shippingexclusionlist.table.name.label')
                ),
            TextColumn::make('exclusions_count')
                ->label(
                    __('lunarpanel.shipping::shippingexclusionlist.table.exclusions_count.label')
                )
                ->counts('exclusions'),
        ];
    }

    public static function getDefaultPages(): array
    {
        return [
            'index' => Pages\ListShippingExclusionLists::route('/'),
            'edit' => Pages\EditShippingExclusionList::route('/{record}/edit'),
        ];
    }
}
