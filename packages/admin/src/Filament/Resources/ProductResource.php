<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\EditProduct;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ListProducts;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductAssociations;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductAvailability;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductCollections;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductIdentifiers;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductInventory;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductMedia;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductPricing;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductShipping;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductUrls;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductVariants;
use Lunar\Admin\Support\RelationManagers\ChannelRelationManager;
use Lunar\Admin\Support\RelationManagers\MediaRelationManager;
use Lunar\Admin\Support\RelationManagers\PriceRelationManager;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Core\Models\Contracts\Product as ProductContract;
use Lunar\Filament\RelationManagers\Product\CustomerGroupPricingRelationManager;
use Lunar\Filament\RelationManagers\Product\CustomerGroupRelationManager;
use Lunar\Filament\Schemas\Product\ProductForm;
use Lunar\Filament\Support\Resolver;
use Lunar\Filament\Tables\Product\ProductTable;
use Lunar\Filament\Widgets\Products\ProductOptionsWidget;
use Lunar\Filament\Widgets\Products\VariantSwitcherTable;

class ProductResource extends BaseResource
{
    protected static ?string $permission = 'catalog:manage-products';

    protected static ?string $model = ProductContract::class;

    protected static ?string $recordTitleAttribute = 'recordTitle';

    protected static ?int $navigationSort = 1;

    protected static int $globalSearchResultsLimit = 5;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;

    public static function getLabel(): string
    {
        return __('lunarpanel::product.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::product.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::products');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.catalog');
    }

    public static function form(Schema $schema): Schema
    {
        return Resolver::form(ProductForm::class, $schema);
    }

    public static function table(Table $table): Table
    {
        return Resolver::table(ProductTable::class, $table);
    }

    protected static function getDefaultSubNavigation(): array
    {
        return [
            EditProduct::class,
            ManageProductAvailability::class,
            ManageProductMedia::class,
            ManageProductPricing::class,
            ManageProductIdentifiers::class,
            ManageProductInventory::class,
            ManageProductShipping::class,
            ManageProductVariants::class,
            ManageProductUrls::class,
            ManageProductCollections::class,
            ManageProductAssociations::class,
        ];
    }

    protected static function getDefaultRelations(): array
    {
        return [
            RelationGroup::make('Availability', [
                ChannelRelationManager::class,
                CustomerGroupRelationManager::class,
            ]),
            MediaRelationManager::class,
            PriceRelationManager::class,
            CustomerGroupPricingRelationManager::class,
        ];
    }

    protected static function getDefaultPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'edit' => EditProduct::route('/{record}/edit'),
            'availability' => ManageProductAvailability::route('/{record}/availability'),
            'identifiers' => ManageProductIdentifiers::route('/{record}/identifiers'),
            'media' => ManageProductMedia::route('/{record}/media'),
            'pricing' => ManageProductPricing::route('/{record}/pricing'),
            'inventory' => ManageProductInventory::route('/{record}/inventory'),
            'shipping' => ManageProductShipping::route('/{record}/shipping'),
            'variants' => ManageProductVariants::route('/{record}/variants'),
            'urls' => ManageProductUrls::route('/{record}/urls'),
            'collections' => ManageProductCollections::route('/{record}/collections'),
            'associations' => ManageProductAssociations::route('/{record}/associations'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            ProductOptionsWidget::class,
            VariantSwitcherTable::class,
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return $record->translateAttribute('name');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'variants.sku',
            'tags.value',
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('variants')
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with([
            'variants',
            'brand',
            'tags',
        ]);
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            __('lunarpanel::product.table.sku.label') => $record->variants->first()->getIdentifier(),
            __('lunarpanel::product.table.stock.label') => $record->variants->first()->stock,
            __('lunarpanel::product.table.brand.label') => $record->brand?->name,
        ];
    }
}
