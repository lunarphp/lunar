<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\BrandResource\Pages;
use Lunar\Admin\Support\Forms\Components\Attributes;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Models\Contracts\Brand;

class BrandResource extends BaseResource
{
    protected static ?string $permission = 'catalog:manage-products';

    protected static ?string $model = Brand::class;

    protected static ?int $navigationSort = 3;

    protected static int $globalSearchResultsLimit = 5;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;

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

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            Pages\EditBrand::class,
            Pages\ManageBrandMedia::class,
            Pages\ManageBrandUrls::class,
            Pages\ManageBrandProducts::class,
            Pages\ManageBrandCollections::class,
        ]);
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
            static::getNameFormComponent(),
        ];
    }

    protected static function getNameFormComponent(): Component
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('lunarpanel::brand.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getAttributeDataFormComponent(): Component
    {
        return Attributes::make()->statePath('attribute_data');
    }

    public static function getDefaultTable(Table $table): Table
    {
        return $table
            ->columns(static::getTableColumns())
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            Tables\Columns\TextColumn::make('name')
                ->label(__('lunarpanel::brand.table.name.label')),
            Tables\Columns\TextColumn::make('products_count')
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
            'media' => Pages\ManageBrandMedia::route('/{record}/media'),
            'urls' => Pages\ManageBrandUrls::route('/{record}/urls'),
            'products' => Pages\ManageBrandProducts::route('/{record}/products'),
            'collections' => Pages\ManageBrandCollections::route('/{record}/collections'),
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
