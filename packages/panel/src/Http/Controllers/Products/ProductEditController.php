<?php

namespace Lunar\Panel\Http\Controllers\Products;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\Products\DeletesProduct;
use Lunar\Core\Contracts\Actions\Products\DuplicatesProduct;
use Lunar\Core\Contracts\Actions\Products\UpdatesProduct;
use Lunar\Core\Exceptions\ProductActionException;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductAssociation;
use Lunar\Core\Models\ProductType;
use Lunar\Core\Models\Url;
use Lunar\Core\States\ProductType\Active;
use Lunar\Panel\Contracts\DraftManager;
use Lunar\Panel\Http\Requests\Products\ProductRequest;
use Lunar\Panel\PanelManager;
use Lunar\Panel\Support\AttributeSchema;
use Lunar\Panel\Support\AvailabilitySchema;
use Spatie\Activitylog\Models\Activity;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductEditController
{
    public function edit(
        Product $product,
        PanelManager $panel,
        DraftManager $drafts,
        AttributeSchema $attributeSchema,
        AvailabilitySchema $availabilitySchema,
    ): Response {
        $availabilitySchema = $availabilitySchema->withPurchasable();

        $product->load(['variants.values.option', 'productType:id,name', 'brand:id,name', 'tags']);

        $staff = $panel->user();
        $draft = $staff ? $drafts->find($product, $staff) : null;

        $media = $product->getMedia(config('lunar.media.collection'))->map(fn (Media $item) => [
            'id' => $item->id,
            'url' => $item->getAvailableUrl(['small']),
            'original_url' => $item->getUrl(),
            'name' => $item->getCustomProperty('name'),
            'alt' => $item->getCustomProperty('alt'),
            'caption' => $item->getCustomProperty('caption'),
            'focal' => $item->getCustomProperty('focal'),
            'primary' => (bool) $item->getCustomProperty('primary'),
            'update_url' => route('panel.products.media.update', [$product, $item]),
            'destroy_url' => route('panel.products.media.destroy', [$product, $item]),
        ])->values();

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
            ->map(fn (Activity $activity) => [
                'description' => $activity->description,
                'created_at' => $activity->created_at,
                'causer_name' => $activity->causer?->full_name ?? $activity->causer?->name ?? null,
            ]);

        $associations = $product->associations()
            ->with(['target.thumbnail', 'target.variants:id,product_id,sku', 'target.brand:id,name'])
            ->get()
            ->groupBy(fn (ProductAssociation $association) => (string) $association->type)
            ->map(fn ($group) => $group->map(fn (ProductAssociation $association) => [
                'id' => $association->id,
                'product_id' => $association->target->id,
                'name' => $association->target->translate('name'),
                'sku' => $association->target->variants->first()?->sku,
                'variants_count' => $association->target->variants->count(),
                'thumbnail' => $association->target->thumbnail?->getAvailableUrl(['small']),
                'status' => $association->target->status->getValue(),
                'destroy_url' => route('panel.products.associations.destroy', [$product, $association]),
            ])->values());

        return Inertia::render('products/Edit', [
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
            'media' => $media,
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
            'associations' => [
                'alternate' => $associations->get('alternate', collect())->values(),
                'cross-sell' => $associations->get('cross-sell', collect())->values(),
                'up-sell' => $associations->get('up-sell', collect())->values(),
            ],
            'storefrontUrl' => config('lunar.panel.storefront_url'),
            'activities' => $activities,
            'urls' => [
                'index' => route('panel.products.index'),
                'update' => route('panel.products.update', $product),
                'destroy' => route('panel.products.destroy', $product),
                'draft' => route('panel.products.draft.update', $product),
                'draftCommit' => route('panel.products.draft.commit', $product),
                'urlsStore' => route('panel.products.urls.store', $product),
                'mediaStore' => route('panel.products.media.store', $product),
                'mediaReorder' => route('panel.products.media.reorder', $product),
                'associationsStore' => route('panel.products.associations.store', $product),
                'collectionsSearch' => route('panel.catalog.collections.search'),
                'productsSearch' => route('panel.catalog.products.search'),
            ],
        ]);
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
