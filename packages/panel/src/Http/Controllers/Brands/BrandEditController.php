<?php

namespace Lunar\Panel\Http\Controllers\Brands;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\Brands\DeletesBrand;
use Lunar\Core\Contracts\Actions\Brands\UpdatesBrand;
use Lunar\Core\Exceptions\BrandActionException;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Url;
use Lunar\Panel\Contracts\DraftManager;
use Lunar\Panel\Http\Requests\Brands\BrandRequest;
use Lunar\Panel\PanelManager;
use Lunar\Panel\Support\AttributeSchema;
use Lunar\Panel\Support\Media\MediaGroups;
use Lunar\Panel\Support\TimelineActivity;
use Spatie\Activitylog\Models\Activity;

class BrandEditController
{
    public function edit(Brand $brand, PanelManager $panel, DraftManager $drafts, AttributeSchema $attributeSchema): Response
    {
        $brand->loadCount(['products', 'collections']);

        $staff = $panel->user();
        $draft = $staff ? $drafts->find($brand, $staff) : null;

        $urls = $brand->urls()
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
                'update_url' => route('panel.brands.urls.update', [$brand, $url]),
                'destroy_url' => route('panel.brands.urls.destroy', [$brand, $url]),
            ]);

        $activities = $brand->activities()
            ->with('causer')
            ->latest()
            ->limit(25)
            ->get()
            ->map(fn (Activity $activity) => TimelineActivity::toArray($activity));

        return Inertia::render('brands/Edit', [
            'brand' => [
                'id' => $brand->id,
                'name' => $brand->name,
                'handle' => $brand->handle,
                'status' => $brand->status->getValue(),
                'short_description' => $brand->short_description?->all() ?: (object) [],
                'description' => $brand->description?->all() ?: (object) [],
                'thumbnail' => $brand->thumbnail?->getAvailableUrl(['small']),
                'products_count' => (int) $brand->getAttribute('products_count'),
                'collections_count' => (int) $brand->getAttribute('collections_count'),
                'created_at' => $brand->created_at,
                'updated_at' => $brand->updated_at,
            ],
            'draft' => $draft ? [
                'data' => $draft->data,
                'updated_at' => $draft->updated_at,
            ] : null,
            'languages' => Language::query()
                ->orderByDesc('default')
                ->orderBy('code')
                ->get(['id', 'code', 'name', 'default']),
            'brandUrls' => $urls,
            'mediaGroups' => MediaGroups::for($brand, 'panel.brands'),
            'attributeGroups' => $attributeSchema->groups($brand),
            'attributeValues' => $attributeSchema->values($brand) ?: (object) [],
            'collections' => $brand->collections()->with('ancestors')->get()->map(fn (Collection $collection) => [
                'id' => $collection->id,
                'name' => $collection->translate('name'),
                'breadcrumb' => $collection->breadcrumb->implode(' > '),
            ])->values(),
            'storefrontUrl' => config('lunar.panel.storefront_url'),
            'activities' => $activities,
            'urls' => [
                'index' => route('panel.brands.index'),
                'activityLog' => route('panel.settings.activity-log.index', ['subject_type' => $brand->getMorphClass()]),
                'update' => route('panel.brands.update', $brand),
                'destroy' => route('panel.brands.destroy', $brand),
                'draft' => route('panel.brands.draft.update', $brand),
                'draftCommit' => route('panel.brands.draft.commit', $brand),
                'urlsStore' => route('panel.brands.urls.store', $brand),
                'collectionsSearch' => route('panel.catalog.collections.search'),
            ],
        ]);
    }

    public function update(BrandRequest $request, Brand $brand, UpdatesBrand $updatesBrand): RedirectResponse
    {
        $updatesBrand->execute($brand, $request->brandAttributes(), $request->collectionIds());

        return back()->with('success', __('panel::brands.flash_updated'));
    }

    public function destroy(Brand $brand, DeletesBrand $deletesBrand): RedirectResponse
    {
        try {
            $deletesBrand->execute($brand);
        } catch (BrandActionException) {
            return back()->with('error', __('panel::brands.flash_delete_protected'));
        }

        return redirect()->route('panel.brands.index')->with('success', __('panel::brands.flash_deleted'));
    }
}
