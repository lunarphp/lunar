<?php

namespace Lunar\Panel\Http\Controllers\ProductTypes;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Lunar\Core\Contracts\Actions\Media\AddsMedia;
use Lunar\Core\Contracts\Actions\Media\DeletesMedia;
use Lunar\Core\Contracts\Actions\Media\ReordersMedia;
use Lunar\Core\Contracts\Actions\Media\UpdatesMedia;
use Lunar\Core\Models\ProductType;
use Lunar\Panel\Http\Controllers\Concerns\HandlesMediaReorder;
use Lunar\Panel\Http\Requests\ProductTypes\ProductTypeMediaStoreRequest;
use Lunar\Panel\Http\Requests\ProductTypes\ProductTypeMediaUpdateRequest;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductTypeMediaController
{
    use HandlesMediaReorder;

    public function store(ProductTypeMediaStoreRequest $request, ProductType $productType, AddsMedia $addsMedia): RedirectResponse
    {
        foreach ($request->validated()['files'] as $file) {
            $addsMedia->execute($productType, $file, $request->resolvedCollection());
        }

        return back()->with('success', __('panel::media.flash_uploaded'));
    }

    public function update(ProductTypeMediaUpdateRequest $request, ProductType $productType, Media $media, UpdatesMedia $updatesMedia): RedirectResponse
    {
        $updatesMedia->execute($media, $request->validated());

        return back()->with('success', __('panel::media.flash_updated'));
    }

    public function reorder(Request $request, ProductType $productType, ReordersMedia $reordersMedia): RedirectResponse
    {
        return $this->reorderMedia($productType, $request, $reordersMedia);
    }

    public function destroy(ProductType $productType, Media $media, DeletesMedia $deletesMedia): RedirectResponse
    {
        $deletesMedia->execute($media);

        return back()->with('success', __('panel::media.flash_deleted'));
    }
}
