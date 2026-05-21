<?php

namespace Lunar\Admin\Filament\Resources;

use Awcodes\Shout\Components\Shout;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
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
use Lunar\Admin\Filament\Resources\ProductResource\RelationManagers\CustomerGroupPricingRelationManager;
use Lunar\Admin\Filament\Resources\ProductResource\RelationManagers\CustomerGroupRelationManager;
use Lunar\Admin\Filament\Resources\ProductResource\Widgets\ProductOptionsWidget;
use Lunar\Admin\Filament\Widgets\Products\VariantSwitcherTable;
use Lunar\Admin\Support\Forms\Components\Attributes;
use Lunar\Admin\Support\Forms\Components\Tags as TagsComponent;
use Lunar\Admin\Support\Forms\Components\TranslatedText as TranslatedTextInput;
use Lunar\Admin\Support\RelationManagers\ChannelRelationManager;
use Lunar\Admin\Support\RelationManagers\MediaRelationManager;
use Lunar\Admin\Support\RelationManagers\PriceRelationManager;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Admin\Support\Tables\Columns\TranslatedTextColumn;
use Lunar\Core\FieldTypes\Text;
use Lunar\Core\FieldTypes\TranslatedText;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\Contracts\Product as ProductContract;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Tag;

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

    public static function getDefaultSubNavigation(): array
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

    public static function getWidgets(): array
    {
        return [
            ProductOptionsWidget::class,
            VariantSwitcherTable::class,
        ];
    }

    protected static function isPublished(?Model $record): bool
    {
        return $record?->status === 'published';
    }

    protected static function hasEnabledCustomerGroup(Model $record): bool
    {
        return $record->customerGroups()->where('enabled', true)->exists();
    }

    protected static function isDefaultGroupVisibleToGuests(Model $record): bool
    {
        $default = CustomerGroup::modelClass()::getDefault();

        return $default && $record->newQuery()
            ->whereKey($record->getKey())
            ->customerGroup($default)
            ->exists();
    }

    public static function getDefaultForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Shout::make('product-status')
                    ->content(
                        __('lunarpanel::product.status.unpublished.content')
                    )->type('info')->hidden(
                        fn (Model $record) => static::isPublished($record)
                    ),
                Shout::make('product-customer-groups')
                    ->content(
                        __('lunarpanel::product.status.availability.customer_groups')
                    )->type('warning')->hidden(fn (Model $record) => ! static::isPublished($record) || static::hasEnabledCustomerGroup($record)
                    ),
                Shout::make('product-no-default-customer-group')
                    ->content(
                        __('lunarpanel::product.status.availability.no_default_customer_group')
                    )->type('warning')->hidden(fn (Model $record) => ! static::isPublished($record)
                        || ! static::hasEnabledCustomerGroup($record)
                        || (bool) CustomerGroup::modelClass()::getDefault()
                    ),
                Shout::make('product-hidden-from-guests')
                    ->content(
                        __('lunarpanel::product.status.availability.hidden_from_guests')
                    )->type('warning')->hidden(fn (Model $record) => ! static::isPublished($record)
                        || ! static::hasEnabledCustomerGroup($record)
                        || ! CustomerGroup::modelClass()::getDefault()
                        || static::isDefaultGroupVisibleToGuests($record)
                    ),
                Shout::make('product-channels')
                    ->content(
                        __('lunarpanel::product.status.availability.channels')
                    )->type('warning')->hidden(fn (Model $record) => ! static::isPublished($record) || $record->channels()->where('enabled', true)->count()
                    ),
                Section::make()
                    ->schema(
                        static::getMainFormComponents(),
                    ),
                static::getAttributeDataFormComponent(),
                static::getVariantAttributeDataFormComponent(),
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
        return static::callStaticLunarHook('extendSkuValidation', [
            'required' => true,
            'unique' => true,
        ]);
    }

    public static function getSkuFormComponent(): Component
    {
        $validation = static::getSkuValidation();

        $input = TextInput::make('sku')
            ->label(__('lunarpanel::product.form.sku.label'))
            ->required($validation['required'] ?? false);

        if ($validation['unique'] ?? false) {
            $input->unique(fn () => (new ProductVariant)->getTable());
        }

        return $input;
    }

    public static function getBasePriceFormComponent(): Component
    {
        $currency = Currency::getDefault();

        return TextInput::make('base_price')->numeric()->prefix(
            $currency->code
        )->rules([
            'min:'.(1 / $currency->factor),
            "decimal:0,{$currency->decimal_places}",
        ])->required();
    }

    public static function getBaseNameFormComponent(): Component
    {
        $nameType = Attribute::whereHandle('name')
            ->whereAttributeType(
                static::getModel()::morphName()
            )
            ->first()?->type ?: TranslatedText::class;

        $component = TranslatedTextInput::make('name');

        if ($nameType == Text::class) {
            $component = TextInput::make('name');
        }

        return $component->label(__('lunarpanel::product.form.name.label'))->required();
    }

    protected static function getBrandFormComponent(): Component
    {
        return Select::make('brand_id')
            ->label(__('lunarpanel::product.form.brand.label'))
            ->relationship('brand', 'name')
            ->searchable()
            ->preload()
            ->createOptionForm([
                TextInput::make('name')
                    ->required(),
            ]);
    }

    public static function getProductTypeFormComponent(): Component
    {
        return Select::make('product_type_id')
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
            ->splitKeys(['Tab', ','])
            ->label(__('lunarpanel::product.form.tags.label'))
            ->helperText(__('lunarpanel::product.form.tags.helper_text'));
    }

    protected static function getAttributeDataFormComponent(): Component
    {
        return Attributes::make();
    }

    protected static function getVariantAttributeDataFormComponent(): Component
    {
        return Attributes::make()
            ->using(ProductVariant::class)
            ->relationship('variant')
            ->hidden(fn (ProductContract $record) => $record->hasVariants);
    }

    public static function getDefaultTable(Table $table): Table
    {
        return $table
            ->columns(static::getTableColumns())
            ->filters([
                SelectFilter::make('brand')
                    ->label(__('lunarpanel::product.table.brand.label'))
                    ->relationship('brand', 'name'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->selectCurrentPageOnly()
            ->deferLoading();
    }

    public static function getTableColumns(): array
    {
        return [
            TextColumn::make('status')
                ->label(__('lunarpanel::product.table.status.label'))
                ->badge()
                ->getStateUsing(
                    fn (Model $record) => $record->deleted_at ? 'deleted' : $record->status
                )
                ->formatStateUsing(fn ($state) => __('lunarpanel::product.table.status.states.'.$state))
                ->color(fn (string $state): string => match ($state) {
                    'draft' => 'warning',
                    'published' => 'success',
                    'deleted' => 'danger',
                    default => 'primary',
                }),
            SpatieMediaLibraryImageColumn::make('thumbnail')
                ->collection(config('lunar.media.collection'))
                ->conversion('small')
                ->filterMediaUsing(fn ($media) => $media->where('custom_properties.primary', true)->count() ? $media->where('custom_properties.primary', true) : $media)
                ->limit(1)
                ->square()
                ->label(''),
            static::getNameTableColumn(),
            TextColumn::make('brand.name')
                ->label(__('lunarpanel::product.table.brand.label'))
                ->toggleable()
                ->searchable(),
            static::getSkuTableColumn(),
            TextColumn::make('variants_sum_stock')
                ->label(__('lunarpanel::product.table.stock.label'))
                ->sum('variants', 'stock'),
            TextColumn::make('productType.name')
                ->label(__('lunarpanel::product.table.producttype.label'))
                ->limit(30)
                ->tooltip(function (TextColumn $column): ?string {
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

    public static function getNameTableColumn(): Column
    {
        return TranslatedTextColumn::make('attribute_data.name')
            ->attributeData()
            ->limitedTooltip()
            ->limit(50)
            ->label(__('lunarpanel::product.table.name.label'))
            ->searchable();
    }

    public static function getSkuTableColumn(): Column
    {
        return TextColumn::make('variants.sku')
            ->label(__('lunarpanel::product.table.sku.label'))
            ->tooltip(function (TextColumn $column, Model $record): ?string {

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
            ->toggleable()
            ->searchable();
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
