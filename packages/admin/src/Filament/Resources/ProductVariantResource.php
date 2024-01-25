<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\ProductVariantResource\Pages;
use Lunar\Admin\Support\Forms\Components\Attributes;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Models\ProductVariant;

class ProductVariantResource extends BaseResource
{
    protected static ?string $permission = 'catalog:manage-products';

    protected static ?string $model = ProductVariant::class;

    public static function getLabel(): string
    {
        return __('lunarpanel::productvariant.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::productvariant.plural_label');
    }

    public static function getDefaultForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema(
                        static::getMainFormComponents(),
                    ),
                static::getAttributeDataFormComponent(),
            ])
            ->columns(1);
    }

    protected static function getMainFormComponents(): array
    {
        return [
            static::getSkuFormComponent(),
            Forms\Components\Group::make([
                static::getGtinFormComponent(),
                static::getEanFormComponent(),
                static::getMpnFormComponent(),
            ])->columns(3),
        ];
    }

    public static function getSkuFormComponent(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('sku');
    }

    public static function getGtinFormComponent(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('gtin')->label(
            __('lunarpanel::product.pages.identifiers.form.gtin.label')
        );
    }

    public static function getMpnFormComponent(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('mpn')->label(
            __('lunarpanel::product.pages.identifiers.form.mpn.label')
        );
    }

    public static function getEanFormComponent(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('ean')->label(
            __('lunarpanel::product.pages.identifiers.form.ean.label')
        );
    }

    protected static function getAttributeDataFormComponent(): Component
    {
        return Attributes::make()->statePath('attribute_data');
    }

    public static function getDefaultTable(Table $table): Table
    {
        return $table
            ->columns(static::getTableColumns())
            ->filters([])
            ->actions([])
            ->bulkActions([])
            ->selectCurrentPageOnly()
            ->deferLoading();
    }

    protected static function getTableColumns(): array
    {
        return [

        ];
    }

    public static function getDefaultRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductVariants::route('/'),
            'edit' => Pages\EditProductVariant::route('/{record}/edit'),
        ];
    }
}
