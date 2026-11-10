<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\ProductVariantResource\Pages\EditProductVariant;
use Lunar\Admin\Filament\Resources\ProductVariantResource\Pages\ListProductVariants;
use Lunar\Admin\Filament\Resources\ProductVariantResource\Pages\ManageVariantIdentifiers;
use Lunar\Admin\Filament\Resources\ProductVariantResource\Pages\ManageVariantInventory;
use Lunar\Admin\Filament\Resources\ProductVariantResource\Pages\ManageVariantMedia;
use Lunar\Admin\Filament\Resources\ProductVariantResource\Pages\ManageVariantPricing;
use Lunar\Admin\Filament\Resources\ProductVariantResource\Pages\ManageVariantShipping;
use Lunar\Admin\Support\Forms\Components\Attributes;
use Lunar\Admin\Support\Forms\Components\TextInputSelectAffix;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Facades\Converter;
use Lunar\Models\Contracts\ProductVariant as ProductVariantContract;
use Lunar\Models\TaxClass;

class ProductVariantResource extends BaseResource
{
    protected static ?string $permission = 'catalog:manage-products';

    protected static ?string $model = ProductVariantContract::class;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;

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

    public static function getDefaultSubNavigation(): array
    {
        return [
            EditProductVariant::class,
            ManageVariantMedia::class,
            ManageVariantPricing::class,
            ManageVariantIdentifiers::class,
            ManageVariantInventory::class,
            ManageVariantShipping::class,
        ];
    }

    public static function getBaseBreadcrumbs(ProductVariantContract $productVariant): array
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

    public static function getDefaultForm(Schema $schema): Schema
    {
        return $schema
            ->components([
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

    public static function getSkuFormComponent(): TextInput
    {
        return TextInput::make('sku');
    }

    public static function getGtinFormComponent(): TextInput
    {
        return TextInput::make('gtin')->label(
            __('lunarpanel::productvariant.form.gtin.label')
        );
    }

    public static function getMpnFormComponent(): TextInput
    {
        return TextInput::make('mpn')->label(
            __('lunarpanel::productvariant.form.mpn.label')
        );
    }

    public static function getEanFormComponent(): TextInput
    {
        return TextInput::make('ean')->label(
            __('lunarpanel::productvariant.form.ean.label')
        );
    }

    public static function getStockFormComponent(): TextInput
    {
        return TextInput::make('stock')
            ->label(
                __('lunarpanel::productvariant.form.stock.label')
            )->numeric();
    }

    public static function getBackorderFormComponent(): TextInput
    {
        return
            TextInput::make('backorder')
                ->label(
                    __('lunarpanel::productvariant.form.backorder.label')
                )->numeric();
    }

    public static function getPurchasableFormComponent(): Select
    {
        return Select::make('purchasable')
            ->options([
                'always' => __('lunarpanel::productvariant.form.purchasable.options.always'),
                'in_stock' => __('lunarpanel::productvariant.form.purchasable.options.in_stock'),
                'in_stock_or_on_backorder' => __('lunarpanel::productvariant.form.purchasable.options.in_stock_or_on_backorder'),
            ])
            ->label(
                __('lunarpanel::productvariant.form.purchasable.label')
            );
    }

    public static function getUnitQtyFormComponent(): TextInput
    {
        return TextInput::make('unit_quantity')
            ->label(
                __('lunarpanel::productvariant.form.unit_quantity.label')
            )->helperText(
                __('lunarpanel::productvariant.form.unit_quantity.helper_text')
            )
            ->numeric()
            ->minValue(1);
    }

    public static function getQuantityIncrementFormComponent(): TextInput
    {
        return TextInput::make('quantity_increment')
            ->label(
                __('lunarpanel::productvariant.form.quantity_increment.label')
            )->helperText(
                __('lunarpanel::productvariant.form.quantity_increment.helper_text')
            )->numeric();
    }

    public static function getMinQuantityFormComponent(): TextInput
    {
        return TextInput::make('min_quantity')
            ->label(
                __('lunarpanel::productvariant.form.min_quantity.label')
            )->helperText(
                __('lunarpanel::productvariant.form.min_quantity.helper_text')
            )->numeric();
    }

    public static function getTaxClassIdFormComponent(): Select
    {
        return Select::make('tax_class_id')
            ->label(
                __('lunarpanel::productvariant.form.tax_class_id.label')
            )
            ->options(
                TaxClass::all()->pluck('name', 'id')
            )->required();
    }

    public static function getTaxRefFormComponent(): TextInput
    {
        return TextInput::make('tax_ref')
            ->label(
                __('lunarpanel::product.pages.pricing.form.tax_ref.label')
            )->helperText(
                __('lunarpanel::product.pages.pricing.form.tax_ref.helper_text')
            );
    }

    public static function getShippableFormComponent(): Toggle
    {
        return Toggle::make('shippable')->label(
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
                fn () => Select::make('length_unit')
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
                fn () => Select::make('width_unit')
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
                fn () => Select::make('height_unit')
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
                fn () => Select::make('weight_unit')
                    ->options(
                        static::getMeasurements('weight')
                    )
                    ->label(
                        __('lunarpanel::productvariant.form.weight_unit.label')
                    )->selectablePlaceholder(false)
            );
    }

    public static function getVariantSwitcherWidget(Model $record): Action
    {
        return Action::make('switch_variant')
            ->label(
                __('lunarpanel::widgets.variant_switcher.label')
            )
            ->modalContent(function () use ($record) {
                return view('lunarpanel::actions.switch-variant', [
                    'record' => $record->product,
                ]);
            })
            ->slideOver();
    }

    protected static function getAttributeDataFormComponent(): Component
    {
        return Attributes::make();
    }

    public static function getDefaultTable(Table $table): Table
    {
        return $table
            ->columns(static::getTableColumns())
            ->filters([])
            ->recordActions([])
            ->toolbarActions([])
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

    public static function getDefaultPages(): array
    {
        return [
            'index' => ListProductVariants::route('/'),
            'edit' => EditProductVariant::route('/{record}/edit'),
            'pricing' => ManageVariantPricing::route('/{record}/pricing'),
            'media' => ManageVariantMedia::route('/{record}/media'),
            'identifiers' => ManageVariantIdentifiers::route('/{record}/identifiers'),
            'inventory' => ManageVariantInventory::route('/{record}/inventory'),
            'shipping' => ManageVariantShipping::route('/{record}/shipping'),
        ];
    }
}
