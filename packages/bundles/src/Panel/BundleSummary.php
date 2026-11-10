<?php

namespace Lunar\Bundles\Panel;

use Illuminate\Support\Collection;
use Lunar\Bundles\Enums\BundlePricing;
use Lunar\Bundles\Models\Bundle;
use Lunar\Bundles\Models\BundleComponent;
use Lunar\Bundles\Models\BundleGroup;
use Lunar\Core\Contracts\Actions\Products\ResolvesInventory;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductOptionValue;
use Lunar\Core\Models\ProductVariant;

/**
 * The JSON the bundle cards read and every mutating endpoint returns, so the
 * editor never has to reconcile a partial response with its own state.
 */
class BundleSummary
{
    public function __construct(
        protected ResolvesInventory $inventory,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function for(ProductVariant $variant): array
    {
        $variant->loadMissing(['product.thumbnail', 'images', 'values.option', 'bundle']);

        /** @var ?Bundle $bundle */
        $bundle = $variant->bundle;

        return [
            'variant' => $this->variantRow($variant),
            'defined' => $bundle !== null,
            'bundle' => $bundle ? $this->bundle($bundle, $variant) : null,
            'default_language' => Language::query()->where('default', true)->value('code') ?? config('app.locale'),
            'urls' => [
                'show' => route('panel.bundles.variants.show', $variant),
                'define' => route('panel.bundles.variants.update', $variant),
                'destroy' => route('panel.bundles.variants.destroy', $variant),
                'components' => route('panel.bundles.variants.components', $variant),
                'groups' => route('panel.bundles.variants.groups', $variant),
                'search' => route('panel.bundles.search-variants', ['exclude' => $variant->getKey()]),
            ],
        ];
    }

    /**
     * The picker row and the component row share this shape so a variant
     * reads the same before and after it is added.
     *
     * @return array{id: int, public_id: string, sku: ?string, name: string, option: ?string, thumbnail: ?string, edit_url: string}
     */
    public function variantRow(ProductVariant $variant): array
    {
        $variant->loadMissing(['product.thumbnail', 'images', 'values.option']);

        return [
            'id' => (int) $variant->getKey(),
            'public_id' => (string) $variant->public_id,
            'sku' => $variant->sku,
            'name' => (string) $variant->product?->translate('name'),
            'option' => $this->optionLabel($variant),
            'thumbnail' => $variant->getThumbnailImage() ?: null,
            'edit_url' => route('panel.products.variants.edit', [$variant->product_id, $variant->getKey()]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function bundle(Bundle $bundle, ProductVariant $variant): array
    {
        $bundle->loadMissing(['groups', 'components.variant.product.thumbnail', 'components.variant.images', 'components.variant.values.option', 'components.variant.prices']);

        $inventory = $this->inventory->execute($variant);

        return [
            'id' => (int) $bundle->getKey(),
            'pricing' => $bundle->pricing->value,
            'discount_percentage' => $bundle->discount_percentage,
            'configurable' => $bundle->groups->isNotEmpty(),
            'components' => $bundle->components->map(fn (BundleComponent $component) => [
                'id' => (int) $component->getKey(),
                'public_id' => (string) $component->public_id,
                'group_id' => $component->bundle_group_id ? (int) $component->bundle_group_id : null,
                'quantity' => (int) $component->quantity,
                'default' => (bool) $component->default,
                'position' => (int) $component->position,
                'variant' => $this->variantRow($component->variant),
            ])->values()->all(),
            'groups' => $bundle->groups->map(fn (BundleGroup $group) => [
                'id' => (int) $group->getKey(),
                'public_id' => (string) $group->public_id,
                'name' => $group->name?->getArrayCopy() ?: (object) [],
                'label' => (string) $group->translate('name'),
                'min_selections' => (int) $group->min_selections,
                'max_selections' => (int) $group->max_selections,
                'position' => (int) $group->position,
            ])->values()->all(),
            'availability' => [
                'available' => $inventory->available,
                'unlimited' => $inventory->unlimited,
            ],
            'prices' => $bundle->pricing === BundlePricing::Components ? $this->derivedPrices($bundle, $variant) : [],
        ];
    }

    /**
     * Per currency: the materialised base price when every default-selection
     * component has a base price, otherwise the components that lack one.
     *
     * @return array<int, array{currency: string, price: ?string, list_price: ?string, missing: array<int, string>}>
     */
    protected function derivedPrices(Bundle $bundle, ProductVariant $variant): array
    {
        $variant->loadMissing('prices');

        $components = $bundle->components
            ->filter(fn (BundleComponent $component) => $component->isFixed() || $component->default)
            ->values();

        return Currency::query()->orderByDesc('default')->orderBy('code')->get()
            ->map(function (Currency $currency) use ($components, $variant) {
                $missing = $components
                    ->reject(fn (BundleComponent $component) => $this->basePrice($component->variant->prices, $currency) !== null)
                    ->map(fn (BundleComponent $component) => $this->componentLabel($component->variant))
                    ->values()
                    ->all();

                $price = $missing === [] ? $this->basePrice($variant->prices, $currency) : null;

                return [
                    'currency' => $currency->code,
                    'price' => $price ? (new PriceValue((int) $price->price, $currency))->format() : null,
                    'list_price' => $price?->list_price ? (new PriceValue((int) $price->list_price, $currency))->format() : null,
                    'missing' => $missing,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Price>  $prices
     */
    protected function basePrice(Collection $prices, Currency $currency): ?Price
    {
        return $prices->first(
            fn (Price $price) => (int) $price->currency_id === (int) $currency->getKey()
                && (int) $price->min_quantity === 1
                && $price->customer_group_id === null
        );
    }

    protected function optionLabel(ProductVariant $variant): ?string
    {
        $label = $variant->values
            ->map(fn (ProductOptionValue $value) => $value->translate('name'))
            ->filter()
            ->implode(' / ');

        return $label !== '' ? $label : null;
    }

    protected function componentLabel(ProductVariant $variant): string
    {
        $name = (string) $variant->product?->translate('name');
        $option = $this->optionLabel($variant);

        return $option ? "{$name} ({$option})" : ($name !== '' ? $name : (string) $variant->sku);
    }
}
