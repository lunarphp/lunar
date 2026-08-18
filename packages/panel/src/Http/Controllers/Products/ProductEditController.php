<?php

namespace Lunar\Panel\Http\Controllers\Products;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\Products\DeletesProduct;
use Lunar\Core\Contracts\Actions\Products\DuplicatesProduct;
use Lunar\Core\Contracts\Actions\Products\UpdatesProduct;
use Lunar\Core\Exceptions\ProductActionException;
use Lunar\Core\Facades\Converter;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductAssociation;
use Lunar\Core\Models\ProductType;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\StockLevel;
use Lunar\Core\Models\TaxClass;
use Lunar\Core\Models\Url;
use Lunar\Core\States\ProductType\Active;
use Lunar\Panel\Contracts\DraftManager;
use Lunar\Panel\Http\Requests\Products\ProductRequest;
use Lunar\Panel\PanelManager;
use Lunar\Panel\Sections\Catalog\ProductDraftResource;
use Lunar\Panel\Support\AttributeSchema;
use Lunar\Panel\Support\AvailabilitySchema;
use Lunar\Panel\Support\Media\MediaGroups;
use Lunar\Panel\Support\TimelineActivity;
use Lunar\Panel\Support\VariantFields;
use Spatie\Activitylog\Models\Activity;

class ProductEditController
{
    public function edit(
        Product $product,
        PanelManager $panel,
        DraftManager $drafts,
        AttributeSchema $attributeSchema,
        AvailabilitySchema $availabilitySchema,
        VariantFields $variantFields,
    ): Response {
        $availabilitySchema = $availabilitySchema->withPurchasable();

        $product->load([
            'variants.values.option',
            'variants.values.media',
            'variants.prices',
            // Eager-loaded so the per-variant getThumbnailImage() below reads
            // from memory rather than lazy-loading images (and the product
            // inverse) once per row.
            'variants.images',
            'productOptions.values',
            'productOptions.values.media',
            'productType:id,name',
            'brand:id,name',
            'tags',
        ]);

        // Hydrate the variant to product inverse so getThumbnail()'s
        // loadMissing('product') is a no-op instead of a per-variant query.
        $product->variants->each->setRelation('product', $product);

        // Simple shape: one variant and no attached options — its fields are
        // edited inline on this page rather than on a variant page. Both counts
        // read the eager-loaded relations rather than re-querying.
        $isSimple = $product->variants->count() === 1 && $product->productOptions->count() === 0;
        $soleVariant = $isSimple ? $product->variants->first() : null;

        $staff = $panel->user();
        $draft = $staff ? $drafts->find($product, $staff) : null;

        $urls = $product->urls()
            ->with('language:id,code,name')
            ->orderByDesc('default')
            ->orderBy('id')
            ->get()
            ->map(fn (Url $url) => [
                'id' => $url->id,
                'slug' => $url->slug,
                'default' => $url->default,
                'language_id' => $url->language_id,
                'language_code' => $url->language->code,
                'update_url' => route('panel.products.urls.update', [$product, $url]),
                'destroy_url' => route('panel.products.urls.destroy', [$product, $url]),
            ]);

        $activities = $product->activities()
            ->with('causer')
            ->latest()
            ->limit(25)
            ->get()
            ->map(fn (Activity $activity) => TimelineActivity::toArray($activity));

        $defaultCurrency = Currency::getDefault();

        $associations = $product->associations()
            ->with(['target.thumbnail', 'target.variants.prices', 'target.brand:id,name'])
            ->get()
            ->groupBy(fn (ProductAssociation $association) => (string) $association->type)
            ->map(fn ($group) => $group->map(fn (ProductAssociation $association) => [
                'id' => $association->id,
                'product_id' => $association->target->id,
                'name' => $association->target->translate('name'),
                'price' => $this->associationFromPrice($association->target, $defaultCurrency),
                'variants_count' => $association->target->variants->count(),
                'thumbnail' => $association->target->thumbnail?->getAvailableUrl(['small']),
                'status' => $association->target->status->getValue(),
                'destroy_url' => route('panel.products.associations.destroy', [$product, $association]),
            ])->values());

        $measurements = Converter::getMeasurements();

        $orderedVariantIds = $this->orderedVariantIds($product);

        $usedValueIds = $product->variants
            ->flatMap(fn (ProductVariant $variant) => $variant->values->pluck('id'))
            ->unique();

        $mediaCollection = config('lunar.media.collection');
        $valuePreview = fn ($value): array => [
            'colour' => $value->meta['colour'] ?? null,
            'swatch' => $value->getFirstMediaUrl($mediaCollection, 'small') ?: ($value->getFirstMediaUrl($mediaCollection) ?: null),
        ];

        // Reads the eager-loaded productOptions.values relation rather than
        // re-querying it per render.
        $attachedOptions = $product->productOptions
            ->map(fn ($option) => [
                'id' => $option->id,
                'name' => $option->translate('name'),
                'type' => $option->type,
                'shared' => (bool) $option->shared,
                'values' => $option->values->sortBy('position')->map(fn ($value) => [
                    'id' => $value->id,
                    'name' => $value->translate('name'),
                    ...$valuePreview($value),
                ])->values(),
                // The persisted selection is whatever the generated variants
                // actually use; pending edits live client-side.
                'selected_value_ids' => $option->values
                    ->filter(fn ($value) => $usedValueIds->contains($value->id))
                    ->pluck('id')
                    ->values(),
            ])->values();

        return Inertia::render('products/Edit', [
            'shape' => $isSimple ? 'simple' : 'multi',
            'attachedOptions' => $attachedOptions,
            'variants' => $product->variants->sortBy('id')->values()->map(fn (ProductVariant $variant) => [
                'id' => $variant->id,
                'label' => $variant->values
                    ->map(fn ($value) => $value->translate('name'))
                    ->filter()
                    ->implode(' / ') ?: ($variant->sku ?? '#'.$variant->id),
                'values' => $variant->values->map(fn ($value) => [
                    'name' => $value->translate('name'),
                    'type' => $value->option?->type ?? 'text',
                    ...$valuePreview($value),
                ])->values(),
                'value_ids' => $variant->values->pluck('id')->sort()->values(),
                'sku' => $variant->sku,
                'price' => $defaultCurrency
                    ? $variant->prices
                        ->first(fn (Price $price) => $price->currency_id === $defaultCurrency->id
                            && $price->customer_group_id === null
                            && $price->min_quantity === 1)
                        ?->price
                    : null,
                'stock' => $variant->stock_available,
                'enabled' => $variant->enabled,
                'locked' => in_array($variant->id, $orderedVariantIds, true),
                'thumbnail' => $variant->getThumbnailImage() ?: null,
                'edit_url' => route('panel.products.variants.edit', [$product, $variant]),
            ]),
            'variant' => $soleVariant ? $this->variantPayload($product, $soleVariant) : null,
            'variantValues' => $soleVariant
                ? collect($variantFields->values($soleVariant))
                    ->mapWithKeys(fn (mixed $value, string $key) => [ProductDraftResource::VARIANT_PREFIX.$key => $value])
                    ->all()
                : (object) [],
            'variantAttributeGroups' => $soleVariant
                ? $this->prefixedGroups($attributeSchema->groups($soleVariant))
                : [],
            'currencies' => Currency::query()->where('enabled', true)
                ->orderByDesc('default')->orderBy('code')
                ->get(['id', 'code', 'decimal_places', 'default']),
            'customerGroups' => CustomerGroup::query()->orderBy('name')->get(['id', 'name']),
            'taxClasses' => TaxClass::query()->orderBy('name')->get(['id', 'name']),
            'measurements' => [
                'length' => array_keys($measurements['length'] ?? []),
                'weight' => array_keys($measurements['weight'] ?? []),
            ],
            'product' => [
                'id' => $product->id,
                'name' => $product->name?->all() ?: (object) [],
                'display_name' => $product->translate('name'),
                'status' => $product->status->getValue(),
                'status_label' => $product->status->label(),
                'product_type_id' => $product->product_type_id,
                'product_type_name' => $product->productType->name,
                'brand_id' => $product->brand_id,
                'short_description' => $product->short_description?->all() ?: (object) [],
                'description' => $product->description?->all() ?: (object) [],
                'thumbnail' => $product->thumbnail?->getAvailableUrl(['small']),
                'sku' => $product->variants->first()?->sku,
                'variants_count' => $product->variants->count(),
                'has_order_history' => $product->hasOrderHistory(),
                'tags' => $product->tags->pluck('value')->values()->all(),
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ],
            'draft' => $draft ? [
                'data' => $draft->data,
                'updated_at' => $draft->updated_at,
            ] : null,
            'languages' => Language::query()
                ->orderByDesc('default')
                ->orderBy('code')
                ->get(['id', 'code', 'name', 'default']),
            'mediaGroups' => MediaGroups::for($product, 'panel.products'),
            'productUrls' => $urls,
            'attributeGroups' => $attributeSchema->groups($product),
            'attributeValues' => $attributeSchema->values($product) ?: (object) [],
            'availability' => $availabilitySchema->rows(),
            'availabilityValues' => $availabilitySchema->values($product) ?: (object) [],
            'brandOptions' => Brand::query()->orderBy('name')->get(['id', 'name'])
                ->map(fn (Brand $brand) => ['value' => $brand->id, 'label' => $brand->name]),
            // Active types plus the product's current one, so a since-drafted
            // type stays selectable on the products that already use it.
            'typeOptions' => ProductType::query()
                ->where('status', Active::$name)
                ->orWhere('id', $product->product_type_id)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (ProductType $type) => ['value' => $type->id, 'label' => $type->name]),
            'collections' => $product->collections()->with('ancestors')->get()->map(fn (Collection $collection) => [
                'id' => $collection->id,
                'name' => $collection->translate('name'),
                'breadcrumb' => $collection->breadcrumb->implode(' > '),
            ])->values(),
            // One group per registered association type (the configured enum,
            // via ProductAssociation::getTypes()), so a consumer's custom type
            // shows here without touching the panel.
            'associations' => collect(ProductAssociation::getTypes())
                ->map(fn (string $label, string $type): array => [
                    'type' => $type,
                    'label' => $label,
                    'entries' => $associations->get($type, collect())->values(),
                ])->values(),
            'storefrontUrl' => config('lunar.panel.storefront_url'),
            'activities' => $activities,
            'urls' => [
                'index' => route('panel.products.index'),
                'activityLog' => route('panel.settings.activity-log.index', ['subject_type' => $product->getMorphClass()]),
                'update' => route('panel.products.update', $product),
                'destroy' => route('panel.products.destroy', $product),
                'draft' => route('panel.products.draft.update', $product),
                'draftCommit' => route('panel.products.draft.commit', $product),
                'urlsStore' => route('panel.products.urls.store', $product),
                'associationsStore' => route('panel.products.associations.store', $product),
                'associationsReorder' => route('panel.products.associations.reorder', $product),
                'collectionsSearch' => route('panel.catalog.collections.search'),
                'productsSearch' => route('panel.catalog.products.search'),
                'productOptionsSearch' => route('panel.catalog.product-options.search'),
                'optionsGenerate' => route('panel.products.options.generate', $product),
                'variantsBulk' => route('panel.products.variants.bulk', $product),
            ],
        ]);
    }

    /**
     * Ids of the product's variants that appear on order lines — the rows a
     * regeneration or bulk delete cannot remove. One query for the set.
     *
     * @return array<int, int>
     */
    protected function orderedVariantIds(Product $product): array
    {
        return OrderLine::query()
            ->where('purchasable_type', (new ProductVariant)->getMorphClass())
            ->whereIn('purchasable_id', $product->variants->modelKeys())
            ->distinct()
            ->pluck('purchasable_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * The lowest base price across the target's variants in the default
     * currency, formatted for the "From ..." label. Null when there is no
     * default currency or the target carries no matching base price.
     */
    protected function associationFromPrice(Product $target, ?Currency $currency): ?string
    {
        if (! $currency) {
            return null;
        }

        return $target->variants
            ->flatMap(fn (ProductVariant $variant) => $variant->prices)
            ->filter(fn (Price $price) => $price->currency_id === $currency->id
                && $price->customer_group_id === null
                && $price->min_quantity === 1)
            ->sortBy('price')
            ->first()
            ?->format('price');
    }

    /**
     * The sole variant's sub-resource payload for the simple shape: price
     * rows, per-location stock, and its endpoint map. Field values travel
     * separately as prefixed draft values.
     *
     * @return array<string, mixed>
     */
    protected function variantPayload(Product $product, ProductVariant $variant): array
    {
        $levels = $variant->stockLevels()->get()->keyBy('location_id');

        return [
            'id' => $variant->id,
            'prices' => $variant->prices()->orderBy('min_quantity')->orderBy('id')->get()
                ->map(fn (Price $price) => [
                    'id' => $price->id,
                    'currency_id' => $price->currency_id,
                    'customer_group_id' => $price->customer_group_id,
                    'min_quantity' => $price->min_quantity,
                    'price' => $price->price,
                    'list_price' => $price->list_price,
                    'update_url' => route('panel.products.variants.prices.update', [$product, $variant, $price]),
                    'destroy_url' => route('panel.products.variants.prices.destroy', [$product, $variant, $price]),
                ])->values(),
            'stock' => [
                'aggregate' => [
                    'on_hand' => $variant->stock_on_hand,
                    'incoming' => $variant->stock_incoming,
                    'committed' => $variant->stock_committed,
                    'reserved' => $variant->stock_reserved,
                    'unavailable' => $variant->stock_unavailable,
                    'available' => $variant->stock_available,
                ],
                'levels' => Location::query()->orderByDesc('default')->orderBy('name')->get()
                    ->map(function (Location $location) use ($levels) {
                        /** @var ?StockLevel $level */
                        $level = $levels->get($location->id);

                        return [
                            'location_id' => $location->id,
                            'location_name' => $location->name,
                            'default' => (bool) $location->default,
                            'on_hand' => (int) ($level?->on_hand ?? 0),
                            'incoming' => (int) ($level?->incoming ?? 0),
                            'committed' => (int) ($level?->committed ?? 0),
                            'unavailable' => (int) ($level?->unavailable ?? 0),
                        ];
                    })->values(),
            ],
            'urls' => [
                'pricesStore' => route('panel.products.variants.prices.store', [$product, $variant]),
                'stockAdjust' => route('panel.products.variants.stock.adjust', [$product, $variant]),
            ],
        ];
    }

    /**
     * Attribute groups whose field keys carry the simple-shape variant
     * prefix, so AttributeFields reads and writes the product draft's
     * variant:attribute:{handle} keys untouched.
     *
     * @param  array<int, array<string, mixed>>  $groups
     * @return array<int, array<string, mixed>>
     */
    protected function prefixedGroups(array $groups): array
    {
        return array_map(function (array $group): array {
            $group['fields'] = array_map(function (array $field): array {
                $field['key'] = ProductDraftResource::VARIANT_PREFIX.$field['key'];

                return $field;
            }, $group['fields']);

            return $group;
        }, $groups);
    }

    public function update(ProductRequest $request, Product $product, UpdatesProduct $updatesProduct): RedirectResponse
    {
        $updatesProduct->execute(
            $product,
            $request->productAttributes(),
            $request->tags(),
            $request->collectionIds(),
        );

        return back()->with('success', __('panel::products.flash_updated'));
    }

    public function destroy(Product $product, DeletesProduct $deletesProduct): RedirectResponse
    {
        try {
            $deletesProduct->execute($product);
        } catch (ProductActionException) {
            return back()->with('error', __('panel::products.flash_delete_protected'));
        }

        return redirect()->route('panel.products.index')->with('success', __('panel::products.flash_deleted'));
    }

    public function duplicate(Product $product, DuplicatesProduct $duplicatesProduct): RedirectResponse
    {
        $duplicate = $duplicatesProduct->execute($product, __('panel::products.duplicate_suffix'));

        return redirect()
            ->route('panel.products.edit', $duplicate)
            ->with('success', __('panel::products.flash_duplicated'));
    }
}
