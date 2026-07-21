<?php

namespace Lunar\Panel\Http\Controllers\ProductTypes;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Lunar\Core\Contracts\Actions\Media\AddsMedia;
use Lunar\Core\Contracts\Actions\Media\DeletesMedia;
use Lunar\Core\Contracts\Actions\Media\ReordersMedia;
use Lunar\Core\Contracts\Actions\Media\UpdatesMedia;
use Lunar\Core\Models\ProductType;
use Lunar\Panel\Http\Requests\ProductTypes\ProductTypeMediaStoreRequest;
use Lunar\Panel\Http\Requests\ProductTypes\ProductTypeMediaUpdateRequest;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductTypeMediaController
{
    public function store(ProductTypeMediaStoreRequest $request, ProductType $productType, AddsMedia $addsMedia): RedirectResponse
    {
        foreach ($request->validated()['files'] as $file) {
            $addsMedia->execute($productType, $file);
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
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        try {
            $reordersMedia->execute($productType, $validated['ids']);
        } catch (InvalidArgumentException) {
            throw ValidationException::withMessages([
                'ids' => __('panel::media.reorder_mismatch'),
            ]);
        }

        return back();
    }

    public function destroy(ProductType $productType, Media $media, DeletesMedia $deletesMedia): RedirectResponse
    {
        $deletesMedia->execute($media);

        return back()->with('success', __('panel::media.flash_deleted'));
    }
}
