<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
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
use Lunar\Admin\Support\Forms\Components\Attributes;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Models\Contracts\Brand as BrandContract;

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

    public static function getDefaultSubNavigation(): array
    {
        return [
            EditBrand::class,
            ManageBrandMedia::class,
            ManageBrandUrls::class,
            ManageBrandProducts::class,
            ManageBrandCollections::class,
        ];
    }

    public static function getDefaultForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
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
            static::getNameFormComponent(),
        ];
    }

    protected static function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('lunarpanel::brand.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getAttributeDataFormComponent(): Component
    {
        return Attributes::make();
    }

    public static function getDefaultTable(Table $table): Table
    {
        return $table
            ->columns(static::getTableColumns())
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])->searchable();
    }

    protected static function getTableColumns(): array
    {
        return [
            SpatieMediaLibraryImageColumn::make('thumbnail')
                ->collection(config('lunar.media.collection'))
                ->conversion('small')
                ->limit(1)
                ->square()
                ->label(''),
            TextColumn::make('name')
                ->label(__('lunarpanel::brand.table.name.label'))
                ->searchable(),
            TextColumn::make('products_count')
                ->counts('products')
                ->formatStateUsing(
                    fn ($state) => number_format($state, 0)
                )
                ->label(__('lunarpanel::brand.table.products_count.label')),
        ];
    }

    public static function getDefaultRelations(): array
    {
        return [

        ];
    }

    public static function getDefaultPages(): array
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
