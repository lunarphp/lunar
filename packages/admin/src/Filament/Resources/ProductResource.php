<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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
use Lunar\Core\Models\Product;
use Lunar\Filament\GlobalSearch\Concerns\HasLunarGlobalSearch;
use Lunar\Filament\GlobalSearch\ProductGlobalSearch;
use Lunar\Filament\RelationManagers\Product\CustomerGroupPricingRelationManager;
use Lunar\Filament\RelationManagers\Product\CustomerGroupRelationManager;
use Lunar\Filament\Schemas\Product\ProductForm;
use Lunar\Filament\Support\Resolver;
use Lunar\Filament\Tables\Product\ProductTable;
use Lunar\Filament\Widgets\Products\ProductOptionsWidget;
use Lunar\Filament\Widgets\Products\VariantSwitcherTable;

class ProductResource extends BaseResource
{
    use HasLunarGlobalSearch;

    protected static string $globalSearch = ProductGlobalSearch::class;

    protected static ?string $permission = 'catalog:manage-products';

    protected static ?string $model = Product::class;

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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('variants')
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
