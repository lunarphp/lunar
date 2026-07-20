<?php

namespace Lunar\Panel\Http\Controllers\Brands;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Lunar\Core\Contracts\Actions\Media\AddsMedia;
use Lunar\Core\Contracts\Actions\Media\DeletesMedia;
use Lunar\Core\Contracts\Actions\Media\ReordersMedia;
use Lunar\Core\Contracts\Actions\Media\UpdatesMedia;
use Lunar\Core\Models\Brand;
use Lunar\Panel\Http\Requests\Brands\BrandMediaStoreRequest;
use Lunar\Panel\Http\Requests\Brands\BrandMediaUpdateRequest;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class BrandMediaController
{
    public function store(BrandMediaStoreRequest $request, Brand $brand, AddsMedia $addsMedia): RedirectResponse
    {
        foreach ($request->validated()['files'] as $file) {
            $addsMedia->execute($brand, $file);
        }

        return back()->with('success', __('panel::media.flash_uploaded'));
    }

    public function update(BrandMediaUpdateRequest $request, Brand $brand, Media $media, UpdatesMedia $updatesMedia): RedirectResponse
    {
        $updatesMedia->execute($media, $request->validated());

        return back()->with('success', __('panel::media.flash_updated'));
    }

    public function reorder(Request $request, Brand $brand, ReordersMedia $reordersMedia): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        try {
            $reordersMedia->execute($brand, $validated['ids']);
        } catch (InvalidArgumentException) {
            throw ValidationException::withMessages([
                'ids' => __('panel::media.reorder_mismatch'),
            ]);
        }

        return back();
    }

    public function destroy(Brand $brand, Media $media, DeletesMedia $deletesMedia): RedirectResponse
    {
        $deletesMedia->execute($media);

        return back()->with('success', __('panel::media.flash_deleted'));
    }
}
