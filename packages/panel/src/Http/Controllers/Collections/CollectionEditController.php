<?php

namespace Lunar\Panel\Http\Controllers\Collections;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\Collections\DeletesCollection;
use Lunar\Core\Contracts\Actions\Collections\MovesCollection;
use Lunar\Core\Contracts\Actions\Collections\UpdatesCollection;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\Url;
use Lunar\Panel\Contracts\DraftManager;
use Lunar\Panel\Http\Requests\Collections\CollectionRequest;
use Lunar\Panel\PanelManager;
use Lunar\Panel\Support\AttributeSchema;
use Lunar\Panel\Support\AvailabilitySchema;
use Lunar\Panel\Support\Media\MediaGroups;
use Lunar\Panel\Support\TimelineActivity;
use Spatie\Activitylog\Models\Activity;

class CollectionEditController
{
    public function edit(
        Collection $collection,
        PanelManager $panel,
        DraftManager $drafts,
        AttributeSchema $attributeSchema,
        AvailabilitySchema $availabilitySchema,
    ): Response {
        $collection->loadCount('products');
        $collection->load('parent');

        $staff = $panel->user();
        $draft = $staff ? $drafts->find($collection, $staff) : null;

        $urls = $collection->urls()
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
                'update_url' => route('panel.collections.urls.update', [$collection, $url]),
                'destroy_url' => route('panel.collections.urls.destroy', [$collection, $url]),
            ]);

        $activities = $collection->activities()
            ->with('causer')
            ->latest()
            ->limit(25)
            ->get()
            ->map(fn (Activity $activity) => TimelineActivity::toArray($activity));

        /** @var ?Collection $parent */
        $parent = $collection->parent;

        // Curated products paginate with their own page parameter so partial
        // reloads after attach/detach/reorder leave the rest of the page alone.
        $products = $collection->products()
            ->with(['thumbnail', 'brand:id,name', 'variants:id,product_id,sku'])
            ->paginate(10, pageName: 'products_page')
            ->withQueryString()
            ->through(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->translate('name'),
                'sku' => $product->variants->first()?->sku,
                'thumbnail' => $product->thumbnail?->getAvailableUrl(['small']),
                'brand' => $product->brand?->name,
                'status' => $product->status->getValue(),
                'position' => (int) $product->getRelationValue('pivot')->position,
                'detach_url' => route('panel.collections.products.detach', [$collection, $product]),
            ]);

        return Inertia::render('collections/Edit', [
            'collection' => [
                'id' => $collection->id,
                'name' => $collection->name?->all() ?: (object) [],
                'display_name' => $collection->translate('name'),
                'handle' => $collection->handle,
                'status' => $collection->status->getValue(),
                'status_label' => $collection->status->label(),
                'sort' => $collection->sort,
                'short_description' => $collection->short_description?->all() ?: (object) [],
                'description' => $collection->description?->all() ?: (object) [],
                'thumbnail' => $collection->thumbnail?->getAvailableUrl(['small']),
                'group_id' => $collection->collection_group_id,
                'parent' => $parent ? [
                    'id' => $parent->id,
                    'name' => $parent->translate('name'),
                    'breadcrumb' => $parent->breadcrumb->implode(' > '),
                ] : null,
                'products_count' => (int) $collection->getAttribute('products_count'),
                'descendants_count' => (int) (($collection->getRgt() - $collection->getLft() - 1) / 2),
                'created_at' => $collection->created_at,
                'updated_at' => $collection->updated_at,
            ],
            'draft' => $draft ? [
                'data' => $draft->data,
                'updated_at' => $draft->updated_at,
            ] : null,
            'languages' => Language::query()
                ->orderByDesc('default')
                ->orderBy('code')
                ->get(['id', 'code', 'name', 'default']),
            'groups' => CollectionGroup::query()->orderBy('name')->get(['id', 'name']),
            'collectionUrls' => $urls,
            'mediaGroups' => MediaGroups::for($collection, 'panel.collections'),
            'products' => $products,
            'attributeGroups' => $attributeSchema->groups($collection),
            'attributeValues' => $attributeSchema->values($collection) ?: (object) [],
            'availability' => $availabilitySchema->rows(),
            'availabilityValues' => $availabilitySchema->values($collection) ?: (object) [],
            'storefrontUrl' => config('lunar.panel.storefront_url'),
            'activities' => $activities,
            'urls' => [
                'index' => route('panel.collections.index'),
                'activityLog' => route('panel.settings.activity-log.index', ['subject_type' => $collection->getMorphClass()]),
                'update' => route('panel.collections.update', $collection),
                'destroy' => route('panel.collections.destroy', $collection),
                'move' => route('panel.collections.move', $collection),
                'draft' => route('panel.collections.draft.update', $collection),
                'draftCommit' => route('panel.collections.draft.commit', $collection),
                'urlsStore' => route('panel.collections.urls.store', $collection),
                'productsAttach' => route('panel.collections.products.attach', $collection),
                'productsReorder' => route('panel.collections.products.reorder', $collection),
                'collectionsSearch' => route('panel.catalog.collections.search'),
                'productsSearch' => route('panel.catalog.products.search'),
            ],
        ]);
    }

    public function update(CollectionRequest $request, Collection $collection, UpdatesCollection $updatesCollection): RedirectResponse
    {
        $updatesCollection->execute($collection, $request->collectionAttributes());

        return back()->with('success', __('panel::collections.flash_updated'));
    }

    public function destroy(
        Request $request,
        Collection $collection,
        DeletesCollection $deletesCollection,
        MovesCollection $movesCollection,
    ): RedirectResponse {
        $hasChildren = $collection->children()->exists();

        if ($hasChildren && ! $request->boolean('reparent')) {
            return back()->with('error', __('panel::collections.flash_delete_has_children'));
        }

        // Children are promoted to the deleted collection's parent — or to
        // root level when it has none — which the confirmation dialog spells
        // out. DeletesCollection re-parents to a node; root promotion moves
        // each child out first.
        $reparentTo = $collection->parent;

        if ($hasChildren && $reparentTo === null) {
            $collection->children()->get()->each(
                fn (Collection $child) => $movesCollection->execute($child, null)
            );

            $collection->refresh();
            $hasChildren = false;
        }

        $deletesCollection->execute($collection, $hasChildren ? $reparentTo : null);

        return redirect()
            ->route('panel.collections.index')
            ->with('success', __('panel::collections.flash_deleted'));
    }
}
