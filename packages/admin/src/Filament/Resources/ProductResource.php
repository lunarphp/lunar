<?php

namespace Lunar\Admin\Filament\Resources;

use Awcodes\Shout\Components\Shout;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\ProductResource\Pages;
use Lunar\Admin\Filament\Resources\ProductResource\RelationManagers\CustomerGroupPricingRelationManager;
use Lunar\Admin\Filament\Resources\ProductResource\RelationManagers\CustomerGroupRelationManager;
use Lunar\Admin\Filament\Resources\ProductResource\Widgets\ProductOptionsWidget;
use Lunar\Admin\Filament\Widgets\Products\VariantSwitcherTable;
use Lunar\Admin\Support\Forms\Components\Attributes;
use Lunar\Admin\Support\Forms\Components\Tags as TagsComponent;
use Lunar\Admin\Support\Forms\Components\TranslatedText;
use Lunar\Admin\Support\RelationManagers\ChannelRelationManager;
use Lunar\Admin\Support\RelationManagers\MediaRelationManager;
use Lunar\Admin\Support\RelationManagers\PriceRelationManager;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Admin\Support\Tables\Columns\TranslatedTextColumn;
use Lunar\Models\Currency;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Lunar\Models\Tag;

class ProductResource extends BaseResource
{
    protected static ?string $permission = 'catalog:manage-products';

    protected static ?string $model = Product::class;

    protected static ?string $recordTitleAttribute = 'recordTitle';

    protected static ?int $navigationSort = 1;

    protected static int $globalSearchResultsLimit = 5;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;

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

    public static function getDefaultSubNavigation(): array
    {
        return [
            Pages\EditProduct::class,
            Pages\ManageProductAvailability::class,
            Pages\ManageProductMedia::class,
            Pages\ManageProductPricing::class,
            Pages\ManageProductIdentifiers::class,
            Pages\ManageProductInventory::class,
            Pages\ManageProductShipping::class,
            Pages\ManageProductVariants::class,
            Pages\ManageProductUrls::class,
            Pages\ManageProductCollections::class,
            Pages\ManageProductAssociations::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            ProductOptionsWidget::class,
            VariantSwitcherTable::class,
        ];
    }

    public static function getDefaultForm(Form $form): Form
    {
        return $form
            ->schema([
                Shout::make('product-status')
                    ->content(
                        __('lunarpanel::product.status.unpublished.content')
                    )->type('info')->hidden(
                        fn (Model $record) => $record?->status == 'published'
                    ),
                Shout::make('product-customer-groups')
                    ->content(
                        __('lunarpanel::product.status.availability.customer_groups')
                    )->type('warning')->hidden(function (Model $record) {
                        return $record->customerGroups()->where('enabled', true)->count();
                    }),
                Shout::make('product-channels')
                    ->content(
                        __('lunarpanel::product.status.availability.channels')
                    )->type('warning')->hidden(function (Model $record) {
                        return $record->channels()->where('enabled', true)->count();
                    }),
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
            static::getBrandFormComponent(),
            static::getProductTypeFormComponent(),
            static::getTagsFormComponent(),
        ];
    }

    public static function getSkuValidation(): array
    {
        return static::callLunarHook('extendSkuValidation', [
            'required' => true,
            'unique' => true,
        ]);
    }

    public static function getSkuFormComponent(): Component
    {
        $validation = static::getSkuValidation();

        $input = Forms\Components\TextInput::make('sku')
            ->label(__('lunarpanel::product.form.sku.label'))
            ->required($validation['required'] ?? false);

        if ($validation['unique'] ?? false) {
            $input->unique(function () {
                return (new ProductVariant)->getTable();
            });
        }

        return $input;
    }

    public static function getBasePriceFormComponent(): Component
    {
        $currency = Currency::getDefault();

        return Forms\Components\TextInput::make('base_price')->numeric()->prefix(
            $currency->code
        )->rules([
            'min:1',
            "decimal:0,{$currency->decimal_places}",
        ])->required();
    }

    public static function getBaseNameFormComponent(): Component
    {
        return TranslatedText::make('name')
            ->label(__('lunarpanel::product.form.name.label'))->required();
    }

    protected static function getBrandFormComponent(): Component
    {
        return Forms\Components\Select::make('brand_id')
            ->label(__('lunarpanel::product.form.brand.label'))
            ->relationship('brand', 'name')
            ->searchable()
            ->preload()
            ->createOptionForm([
                Forms\Components\TextInput::make('name')
                    ->required(),
            ]);
    }

    public static function getProductTypeFormComponent(): Component
    {
        return Forms\Components\Select::make('product_type_id')
            ->label(__('lunarpanel::product.form.producttype.label'))
            ->relationship('productType', 'name')
            ->searchable()
            ->preload()
            ->live()
            ->required();
    }

    protected static function getTagsFormComponent(): Component
    {
        return TagsComponent::make('tags')
            ->suggestions(Tag::all()->pluck('value')->all())
            ->label(__('lunarpanel::product.form.tags.label'));
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
                Tables\Filters\SelectFilter::make('brand')
                    ->relationship('brand', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->selectCurrentPageOnly()
            ->deferLoading();
    }

    public static function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('status')
                ->label(__('lunarpanel::product.table.status.label'))
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'draft' => 'warning',
                    'published' => 'success',
                }),
            SpatieMediaLibraryImageColumn::make('thumbnail')
                ->collection('images')
                ->conversion('small')
                ->limit(1)
                ->square()
                ->label(''),
            TranslatedTextColumn::make('attribute_data.name')
                ->attributeData()
                ->limitedTooltip()
                ->limit(50)
                ->label(__('lunarpanel::product.table.name.label')),
            Tables\Columns\TextColumn::make('brand.name')
                ->label(__('lunarpanel::product.table.brand.label'))
                ->toggleable()
                ->searchable(),
            Tables\Columns\TextColumn::make('variants.sku')
                ->label(__('lunarpanel::product.table.sku.label'))
                ->tooltip(function (Tables\Columns\TextColumn $column, Model $record): ?string {

                    if ($record->variants->count() <= $column->getListLimit()) {
                        return null;
                    }

                    if ($record->variants->count() > 30) {
                        $record->variants = $record->variants->slice(0, 30);
                    }

                    return $record->variants
                        ->map(fn ($variant) => $variant->sku)
                        ->implode(', ');
                })
                ->listWithLineBreaks()
                ->limitList(1)
                ->toggleable(),
            Tables\Columns\TextColumn::make('variants_sum_stock')
                ->label(__('lunarpanel::product.table.stock.label'))
                ->sum('variants', 'stock'),
            Tables\Columns\TextColumn::make('productType.name')
                ->label(__('lunarpanel::product.table.producttype.label'))
                ->limit(30)
                ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                    $state = $column->getState();

                    if (strlen($state) <= $column->getCharacterLimit()) {
                        return null;
                    }

                    // Only render the tooltip if the column contents exceeds the length limit.
                    return $state;
                })
                ->toggleable(),
        ];
    }

    public static function getDefaultRelations(): array
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

    public static function getDefaultPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
            'availability' => Pages\ManageProductAvailability::route('/{record}/availability'),
            'identifiers' => Pages\ManageProductIdentifiers::route('/{record}/identifiers'),
            'media' => Pages\ManageProductMedia::route('/{record}/media'),
            'pricing' => Pages\ManageProductPricing::route('/{record}/pricing'),
            'inventory' => Pages\ManageProductInventory::route('/{record}/inventory'),
            'shipping' => Pages\ManageProductShipping::route('/{record}/shipping'),
            'variants' => Pages\ManageProductVariants::route('/{record}/variants'),
            'urls' => Pages\ManageProductUrls::route('/{record}/urls'),
            'collections' => Pages\ManageProductCollections::route('/{record}/collections'),
            'associations' => Pages\ManageProductAssociations::route('/{record}/associations'),
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
