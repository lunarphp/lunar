<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\BrandResource\Pages\CreateBrand;
use Lunar\Admin\Filament\Resources\BrandResource\Pages\EditBrand;
use Lunar\Admin\Filament\Resources\BrandResource\Pages\ListBrands;
use Lunar\Admin\Filament\Resources\BrandResource\Pages\ManageBrandCollections;
use Lunar\Admin\Filament\Resources\BrandResource\Pages\ManageBrandMedia;
use Lunar\Admin\Filament\Resources\BrandResource\Pages\ManageBrandProducts;
use Lunar\Admin\Filament\Resources\BrandResource\Pages\ManageBrandUrls;
use Lunar\Admin\Filament\Resources\BrandResource\Schemas\BrandForm;
use Lunar\Admin\Filament\Resources\BrandResource\Tables\BrandTable;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Core\Models\Contracts\Brand as BrandContract;

class BrandResource extends BaseResource
{
    protected static ?string $permission = 'catalog:manage-products';

    protected static ?string $model = BrandContract::class;

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
        return BrandForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BrandTable::configure($table);
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            EditBrand::class,
            ManageBrandMedia::class,
            ManageBrandUrls::class,
            ManageBrandProducts::class,
            ManageBrandCollections::class,
        ]);
    }

    public static function getPages(): array
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

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return $record->name;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name',
        ];
    }
}
