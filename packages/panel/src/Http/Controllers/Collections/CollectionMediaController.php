<?php

namespace Lunar\Panel\Http\Controllers\Collections;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Lunar\Core\Contracts\Actions\Media\AddsMedia;
use Lunar\Core\Contracts\Actions\Media\DeletesMedia;
use Lunar\Core\Contracts\Actions\Media\ReordersMedia;
use Lunar\Core\Contracts\Actions\Media\UpdatesMedia;
use Lunar\Core\Models\Collection;
use Lunar\Panel\Http\Requests\Collections\CollectionMediaStoreRequest;
use Lunar\Panel\Http\Requests\Collections\CollectionMediaUpdateRequest;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CollectionMediaController
{
    public function store(CollectionMediaStoreRequest $request, Collection $collection, AddsMedia $addsMedia): RedirectResponse
    {
        foreach ($request->validated()['files'] as $file) {
            $addsMedia->execute($collection, $file);
        }

        return back()->with('success', __('panel::media.flash_uploaded'));
    }

    public function update(CollectionMediaUpdateRequest $request, Collection $collection, Media $media, UpdatesMedia $updatesMedia): RedirectResponse
    {
        $updatesMedia->execute($media, $request->validated());

        return back()->with('success', __('panel::media.flash_updated'));
    }

    public function reorder(Request $request, Collection $collection, ReordersMedia $reordersMedia): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        try {
            $reordersMedia->execute($collection, $validated['ids']);
        } catch (InvalidArgumentException) {
            throw ValidationException::withMessages([
                'ids' => __('panel::media.reorder_mismatch'),
            ]);
        }

        return back();
    }

    public function destroy(Collection $collection, Media $media, DeletesMedia $deletesMedia): RedirectResponse
    {
        $deletesMedia->execute($media);

        return back()->with('success', __('panel::media.flash_deleted'));
    }
}
