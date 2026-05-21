<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\ProductTypeResource\Pages\CreateProductType;
use Lunar\Admin\Filament\Resources\ProductTypeResource\Pages\EditProductType;
use Lunar\Admin\Filament\Resources\ProductTypeResource\Pages\ListProductTypes;
use Lunar\Admin\Filament\Resources\ProductTypeResource\Schemas\ProductTypeForm;
use Lunar\Admin\Filament\Resources\ProductTypeResource\Tables\ProductTypeTable;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Core\Models\Contracts\ProductType as ProductTypeContract;

class ProductTypeResource extends BaseResource
{
    protected static ?string $permission = 'catalog:manage-products';

    protected static ?string $model = ProductTypeContract::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-swatch';

    protected static ?int $navigationSort = 2;

    public static function getLabel(): string
    {
        return __('lunarpanel::producttype.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::producttype.plural_label');
    }

    public static function getNavigationParentItem(): ?string
    {
        return __('lunarpanel::product.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.catalog');
    }

    public static function form(Schema $schema): Schema
    {
        return ProductTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductTypeTable::configure($table);
    }

    protected static function getDefaultPages(): array
    {
        return [
            'index' => ListProductTypes::route('/'),
            'create' => CreateProductType::route('/create'),
            'edit' => EditProductType::route('/{record}/edit'),
        ];
    }
}
