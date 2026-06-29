<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\BrandResource\Pages\CreateBrand;
use Lunar\Admin\Filament\Resources\BrandResource\Pages\EditBrand;
use Lunar\Admin\Filament\Resources\BrandResource\Pages\ListBrands;
use Lunar\Admin\Filament\Resources\BrandResource\Pages\ManageBrandCollections;
use Lunar\Admin\Filament\Resources\BrandResource\Pages\ManageBrandMedia;
use Lunar\Admin\Filament\Resources\BrandResource\Pages\ManageBrandProducts;
use Lunar\Admin\Filament\Resources\BrandResource\Pages\ManageBrandUrls;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Core\Models\Brand;
use Lunar\Filament\GlobalSearch\BrandGlobalSearch;
use Lunar\Filament\GlobalSearch\Concerns\HasLunarGlobalSearch;
use Lunar\Filament\Schemas\Brand\BrandForm;
use Lunar\Filament\Support\Resolver;
use Lunar\Filament\Tables\Brand\BrandTable;

class BrandResource extends BaseResource
{
    use HasLunarGlobalSearch;

    protected static string $globalSearch = BrandGlobalSearch::class;

    protected static ?string $permission = 'catalog:manage-products';

    protected static ?string $model = Brand::class;

    protected static ?int $navigationSort = 3;

    protected static int $globalSearchResultsLimit = 5;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;

    public static function getLabel(): string
    {
        return __('lunarpanel::brand.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::brand.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::brands');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.catalog');
    }

    public static function form(Schema $schema): Schema
    {
        return Resolver::form(BrandForm::class, $schema);
    }

    public static function table(Table $table): Table
    {
        return Resolver::table(BrandTable::class, $table);
    }

    protected static function getDefaultSubNavigation(): array
    {
        return [
            EditBrand::class,
            ManageBrandMedia::class,
            ManageBrandUrls::class,
            ManageBrandProducts::class,
            ManageBrandCollections::class,
        ];
    }

    protected static function getDefaultPages(): array
    {
        return [
            'index' => ListBrands::route('/'),
            'create' => CreateBrand::route('/create'),
            'edit' => EditBrand::route('/{record}/edit'),
            'media' => ManageBrandMedia::route('/{record}/media'),
            'urls' => ManageBrandUrls::route('/{record}/urls'),
            'products' => ManageBrandProducts::route('/{record}/products'),
            'collections' => ManageBrandCollections::route('/{record}/collections'),
        ];
    }
}
