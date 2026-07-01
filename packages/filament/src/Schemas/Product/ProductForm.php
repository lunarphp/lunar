<?php

namespace Lunar\Filament\Schemas\Product;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Tag;
use Lunar\Filament\Forms\Components\Attributes;
use Lunar\Filament\Forms\Components\BrandSelect;
use Lunar\Filament\Forms\Components\ProductTypeSelect;
use Lunar\Filament\Forms\Components\Tags as TagsComponent;
use Lunar\Filament\Forms\Components\TranslatedText as TranslatedTextInput;
use Lunar\Filament\Support\Concerns\CallsHooks;

class ProductForm
{
    use CallsHooks;

    public static function configure(Schema $schema): Schema
    {
        return self::callStaticLunarHook(
            'configureForm',
            $schema
                ->components([
                    ...static::getStatusShouts(),
                    Section::make()->schema(static::getMainComponents()),
                    static::getAttributeDataComponent(),
                    static::getVariantAttributeDataComponent(),
                ])
                ->columns(1),
        );
    }

    public static function getMainComponents(): array
    {
        return [
            static::getBaseNameComponent(),
            static::getShortDescriptionComponent(),
            static::getDescriptionComponent(),
            static::getBrandComponent(),
            static::getProductTypeComponent(),
            static::getTagsComponent(),
        ];
    }

    public static function getStatusShouts(): array
    {
        return [
            Callout::make()
                ->heading(__('lunar-filament::product.status.draft.content'))
                ->status('info')
                ->hidden(fn (Model $record) => ! static::isDraft($record)),
            Callout::make()
                ->heading(__('lunar-filament::product.status.archived.content'))
                ->status('warning')
                ->hidden(fn (Model $record) => ! static::isArchived($record)),
            Callout::make()
                ->heading(__('lunar-filament::product.status.availability.customer_groups'))
                ->status('warning')
                ->hidden(fn (Model $record) => ! static::isPublished($record) || static::hasEnabledCustomerGroup($record)),
            Callout::make()
                ->heading(__('lunar-filament::product.status.availability.no_default_customer_group'))
                ->status('warning')
                ->hidden(fn (Model $record) => ! static::isPublished($record)
                    || ! static::hasEnabledCustomerGroup($record)
                    || (bool) CustomerGroup::getDefault()
                ),
            Callout::make()
                ->heading(__('lunar-filament::product.status.availability.hidden_from_guests'))
                ->status('warning')
                ->hidden(fn (Model $record) => ! static::isPublished($record)
                    || ! static::hasEnabledCustomerGroup($record)
                    || ! CustomerGroup::getDefault()
                    || static::isDefaultGroupVisibleToGuests($record)
                ),
            Callout::make()
                ->heading(__('lunar-filament::product.status.availability.channels'))
                ->status('warning')
                ->hidden(fn (Model $record) => ! static::isPublished($record) || $record->channels()->where('enabled', true)->count()),
        ];
    }

    public static function getSkuValidation(): array
    {
        return self::callStaticLunarHook('extendSkuValidation', [
            'required' => true,
            'unique' => true,
        ]);
    }

    public static function getSkuComponent(): Component
    {
        $validation = static::getSkuValidation();

        $input = TextInput::make('sku')
            ->label(__('lunar-filament::product.form.sku.label'))
            ->required($validation['required'] ?? false);

        if ($validation['unique'] ?? false) {
            $input->unique(fn () => (new ProductVariant)->getTable());
        }

        return $input;
    }

    public static function getBasePriceComponent(): Component
    {
        $currency = Currency::getDefault();

        return TextInput::make('base_price')
            ->numeric()
            ->prefix($currency->code)
            ->rules([
                'min:'.(1 / $currency->factor),
                "decimal:0,{$currency->decimal_places}",
            ])
            ->required();
    }

    public static function getBaseNameComponent(): Component
    {
        return TranslatedTextInput::make('name')
            ->label(__('lunar-filament::product.form.name.label'))
            ->required();
    }

    public static function getShortDescriptionComponent(): Component
    {
        return TranslatedTextInput::make('short_description')
            ->label(__('lunar-filament::product.form.short_description.label'));
    }

    public static function getDescriptionComponent(): Component
    {
        return TranslatedTextInput::make('description')
            ->label(__('lunar-filament::product.form.description.label'))
            ->optionRichtext(true);
    }

    public static function getBrandComponent(): Component
    {
        return BrandSelect::make('brand_id')
            ->label(__('lunar-filament::product.form.brand.label'));
    }

    public static function getProductTypeComponent(): Component
    {
        return ProductTypeSelect::make('product_type_id')
            ->label(__('lunar-filament::product.form.producttype.label'))
            ->live()
            ->required();
    }

    public static function getTagsComponent(): Component
    {
        return TagsComponent::make('tags')
            ->suggestions(Tag::all()->pluck('value')->all())
            ->splitKeys(['Tab', ','])
            ->label(__('lunar-filament::product.form.tags.label'))
            ->helperText(__('lunar-filament::product.form.tags.helper_text'));
    }

    public static function getAttributeDataComponent(): Component
    {
        return Attributes::make();
    }

    public static function getVariantAttributeDataComponent(): Component
    {
        return Attributes::make()
            ->using(ProductVariant::class)
            ->relationship('variant')
            ->hidden(fn (Product $record) => $record->hasVariants);
    }

    protected static function isPublished(?Model $record): bool
    {
        return $record !== null && (string) $record->status === 'published';
    }

    protected static function isDraft(?Model $record): bool
    {
        return $record !== null && (string) $record->status === 'draft';
    }

    protected static function isArchived(?Model $record): bool
    {
        return $record !== null && (string) $record->status === 'archived';
    }

    protected static function hasEnabledCustomerGroup(Model $record): bool
    {
        return $record->customerGroups()->where('enabled', true)->exists();
    }

    protected static function isDefaultGroupVisibleToGuests(Model $record): bool
    {
        $default = CustomerGroup::getDefault();

        return $default && $record->newQuery()
            ->whereKey($record->getKey())
            ->customerGroup($default)
            ->exists();
    }
}
