<?php

namespace Lunar\Panel\Http\Controllers\Collections;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Lunar\Core\Contracts\Actions\Media\AddsMedia;
use Lunar\Core\Contracts\Actions\Media\DeletesMedia;
use Lunar\Core\Contracts\Actions\Media\ReordersMedia;
use Lunar\Core\Contracts\Actions\Media\UpdatesMedia;
use Lunar\Core\Models\Collection;
use Lunar\Panel\Http\Controllers\Concerns\HandlesMediaReorder;
use Lunar\Panel\Http\Requests\Collections\CollectionMediaStoreRequest;
use Lunar\Panel\Http\Requests\Collections\CollectionMediaUpdateRequest;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CollectionMediaController
{
    use HandlesMediaReorder;

    public function store(CollectionMediaStoreRequest $request, Collection $collection, AddsMedia $addsMedia): RedirectResponse
    {
        foreach ($request->validated()['files'] as $file) {
            $addsMedia->execute($collection, $file, $request->resolvedCollection());
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
        return $this->reorderMedia($collection, $request, $reordersMedia);
    }

    public function destroy(Collection $collection, Media $media, DeletesMedia $deletesMedia): RedirectResponse
    {
        $deletesMedia->execute($media);

        return back()->with('success', __('panel::media.flash_deleted'));
    }
}
