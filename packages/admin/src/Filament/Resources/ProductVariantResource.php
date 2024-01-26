<?php

namespace Lunar\Admin\Filament\Resources;

use Cartalyst\Converter\Laravel\Facades\Converter;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\ProductVariantResource\Pages;
use Lunar\Admin\Support\Forms\Components\Attributes;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Models\ProductVariant;
use Marvinosswald\FilamentInputSelectAffix\TextInputSelectAffix;

class ProductVariantResource extends BaseResource
{
    protected static ?string $permission = 'catalog:manage-products';

    protected static ?string $model = ProductVariant::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;

    public static function getLabel(): string
    {
        return __('lunarpanel::productvariant.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::productvariant.plural_label');
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return false;
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            Pages\EditProductVariant::class,
            Pages\ManageVariantIdentifiers::class,
            Pages\ManageVariantInventory::class,
            Pages\ManageVariantShipping::class,
            Pages\ManageVariantPricing::class,
        ]);
    }

    public static function getBaseBreadcrumbs(ProductVariant $productVariant): array
    {
        return [
            ProductResource::getUrl('edit', [
                'record' => $productVariant->product,
            ]) => $productVariant->product->attr('name'),
            ProductResource::getUrl('variants', [
                'record' => $productVariant->product,
            ]) => 'Variants',
            ProductVariantResource::getUrl('edit', [
                'record' => $productVariant,
            ]) => $productVariant->sku,
        ];
    }

    public static function getDefaultForm(Form $form): Form
    {
        return $form
            ->schema([
                static::getAttributeDataFormComponent(),
            ])
            ->columns(1);
    }

    protected static function getMainFormComponents(): array
    {
        return [
            static::getSkuFormComponent(),

        ];
    }

    public static function getSkuFormComponent(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('sku');
    }

    public static function getGtinFormComponent(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('gtin')->label(
            __('lunarpanel::productvariant.form.gtin.label')
        );
    }

    public static function getMpnFormComponent(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('mpn')->label(
            __('lunarpanel::productvariant.form.mpn.label')
        );
    }

    public static function getEanFormComponent(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('ean')->label(
            __('lunarpanel::productvariant.form.ean.label')
        );
    }

    public static function getStockFormComponent(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('stock')
            ->label(
                __('lunarpanel::productvariant.form.stock.label')
            )->numeric();
    }

    public static function getBackorderFormComponent(): Forms\Components\TextInput
    {
        return
            Forms\Components\TextInput::make('backorder')
                ->label(
                    __('lunarpanel::productvariant.form.backorder.label')
                )->numeric();
    }

    public static function getPurchasableFormComponent(): Forms\Components\Select
    {
        return Forms\Components\Select::make('purchasable')
            ->options([
                'always' => __('lunarpanel::productvariant.form.purchasable.options.always'),
                'in_stock' => __('lunarpanel::productvariant.form.purchasable.options.in_stock'),
                'backorder' => __('lunarpanel::productvariant.form.purchasable.options.backorder'),
            ])
            ->label(
                __('lunarpanel::productvariant.form.purchasable.label')
            );
    }

    public static function getUnitQtyFormComponent(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('unit_quantity')
            ->label(
                __('lunarpanel::productvariant.form.unit_quantity.label')
            )->helperText(
                __('lunarpanel::productvariant.form.unit_quantity.helper_text')
            )->numeric();
    }

    public static function getQuantityIncrementFormComponent(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('quantity_increment')
            ->label(
                __('lunarpanel::productvariant.form.quantity_increment.label')
            )->helperText(
                __('lunarpanel::productvariant.form.quantity_increment.helper_text')
            )->numeric();
    }

    public static function getMinQuantityFormComponent(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('min_quantity')
            ->label(
                __('lunarpanel::productvariant.form.min_quantity.label')
            )->helperText(
                __('lunarpanel::productvariant.form.min_quantity.helper_text')
            )->numeric();
    }

    public static function getShippableFormComponent(): Forms\Components\Toggle
    {
        return Forms\Components\Toggle::make('shippable')->label(
            __('lunarpanel::productvariant.form.shippable.label')
        )->columnSpan(2);
    }

    public static function getMeasurements($key = null): array
    {
        $measurements = Converter::getMeasurements();

        return collect(
            array_keys($measurements[$key] ?? [])
        )->mapWithKeys(
            fn ($value) => [$value => $value]
        )->toArray();
    }

    public static function getLengthFormComponent(): TextInputSelectAffix
    {
        return TextInputSelectAffix::make('length_value')
            ->label(
                __('lunarpanel::productvariant.form.length_value.label')
            )
            ->numeric()
            ->select(
                fn () => Forms\Components\Select::make('length_unit')
                    ->options(
                        static::getMeasurements('length')
                    )
                    ->label(
                        __('lunarpanel::productvariant.form.length_unit.label')
                    )->selectablePlaceholder(false)
            );
    }

    public static function getWidthFormComponent(): TextInputSelectAffix
    {
        return TextInputSelectAffix::make('width_value')
            ->label(
                __('lunarpanel::productvariant.form.width_value.label')
            )
            ->numeric()
            ->select(
                fn () => Forms\Components\Select::make('width_unit')
                    ->options(
                        static::getMeasurements('length')
                    )
                    ->label(
                        __('lunarpanel::productvariant.form.width_unit.label')
                    )->selectablePlaceholder(false)
            );
    }

    public static function getHeightFormComponent(): TextInputSelectAffix
    {
        return TextInputSelectAffix::make('height_value')
            ->label(
                __('lunarpanel::productvariant.form.height_value.label')
            )
            ->numeric()
            ->select(
                fn () => Forms\Components\Select::make('height_unit')
                    ->options(
                        static::getMeasurements('length')
                    )
                    ->label(
                        __('lunarpanel::productvariant.form.height_unit.label')
                    )->selectablePlaceholder(false)
            );
    }

    public static function getWeightFormComponent(): TextInputSelectAffix
    {
        return TextInputSelectAffix::make('weight_value')
            ->label(
                __('lunarpanel::productvariant.form.weight_value.label')
            )
            ->numeric()
            ->select(
                fn () => Forms\Components\Select::make('weight_unit')
                    ->options(
                        static::getMeasurements('weight')
                    )
                    ->label(
                        __('lunarpanel::productvariant.form.weight_unit.label')
                    )->selectablePlaceholder(false)
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
            'identifiers' => Pages\ManageVariantIdentifiers::route('/{record}/identifiers'),
            'inventory' => Pages\ManageVariantInventory::route('/{record}/inventory'),
            'shipping' => Pages\ManageVariantShipping::route('/{record}/shipping'),
            'pricing' => Pages\ManageVariantPricing::route('/{record}/pricing'),
        ];
    }
}
