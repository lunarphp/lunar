<?php

namespace Lunar\Panel\Http\Controllers\Products;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Lunar\Core\Contracts\Actions\Media\AddsMedia;
use Lunar\Core\Contracts\Actions\Media\DeletesMedia;
use Lunar\Core\Contracts\Actions\Media\ReordersMedia;
use Lunar\Core\Contracts\Actions\Media\UpdatesMedia;
use Lunar\Core\Models\Product;
use Lunar\Panel\Http\Controllers\Concerns\HandlesMediaReorder;
use Lunar\Panel\Http\Requests\Products\ProductMediaStoreRequest;
use Lunar\Panel\Http\Requests\Products\ProductMediaUpdateRequest;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductMediaController
{
    use HandlesMediaReorder;

    public function store(ProductMediaStoreRequest $request, Product $product, AddsMedia $addsMedia): RedirectResponse
    {
        foreach ($request->validated()['files'] as $file) {
            $addsMedia->execute($product, $file, $request->resolvedCollection());
        }

        return back()->with('success', __('panel::media.flash_uploaded'));
    }

    public function update(ProductMediaUpdateRequest $request, Product $product, Media $media, UpdatesMedia $updatesMedia): RedirectResponse
    {
        $updatesMedia->execute($media, $request->validated());

        return back()->with('success', __('panel::media.flash_updated'));
    }

    public function reorder(Request $request, Product $product, ReordersMedia $reordersMedia): RedirectResponse
    {
        return $this->reorderMedia($product, $request, $reordersMedia);
    }

    public function destroy(Product $product, Media $media, DeletesMedia $deletesMedia): RedirectResponse
    {
        $deletesMedia->execute($media);

        return back()->with('success', __('panel::media.flash_deleted'));
    }
}
