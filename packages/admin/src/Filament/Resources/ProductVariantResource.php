<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Actions\Action;
use Filament\Pages\Enums\SubNavigationPosition;
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
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Core\Models\ProductVariant;
use Lunar\Filament\Schemas\ProductVariant\ProductVariantForm;
use Lunar\Filament\Support\Resolver;
use Lunar\Filament\Tables\ProductVariant\ProductVariantTable;

class ProductVariantResource extends BaseResource
{
    protected static ?string $permission = 'catalog:manage-products';

    protected static ?string $model = ProductVariant::class;

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

    public static function getBaseBreadcrumbs(ProductVariant $productVariant): array
    {
        return [
            ProductResource::getUrl('edit', [
                'record' => $productVariant->product,
            ]) => $productVariant->product->translate('name'),
            ProductResource::getUrl('variants', [
                'record' => $productVariant->product,
            ]) => 'Variants',
            ProductVariantResource::getUrl('edit', [
                'record' => $productVariant,
            ]) => $productVariant->sku,
        ];
    }

    public static function getVariantSwitcherWidget(Model $record): Action
    {
        return Action::make('switch_variant')
            ->label(__('lunar-filament::widgets.variant_switcher.label'))
            ->modalContent(function () use ($record) {
                return view('lunarpanel::actions.switch-variant', [
                    'record' => $record->product,
                ]);
            })
            ->slideOver();
    }

    public static function form(Schema $schema): Schema
    {
        return Resolver::form(ProductVariantForm::class, $schema);
    }

    public static function table(Table $table): Table
    {
        return Resolver::table(ProductVariantTable::class, $table);
    }

    protected static function getDefaultSubNavigation(): array
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

    protected static function getDefaultPages(): array
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
