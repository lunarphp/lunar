<?php

namespace Lunar\Panel\Http\Controllers\Collections;

use Illuminate\Http\RedirectResponse;
use Lunar\Core\Contracts\Actions\Urls\CreatesUrl;
use Lunar\Core\Contracts\Actions\Urls\DeletesUrl;
use Lunar\Core\Contracts\Actions\Urls\UpdatesUrl;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Url;
use Lunar\Panel\Http\Requests\Collections\CollectionUrlRequest;

class CollectionUrlController
{
    public function store(CollectionUrlRequest $request, Collection $collection, CreatesUrl $createsUrl): RedirectResponse
    {
        $createsUrl->execute($collection, $request->validated());

        return back()->with('success', __('panel::urls.flash_created'));
    }

    public function update(CollectionUrlRequest $request, Collection $collection, Url $url, UpdatesUrl $updatesUrl): RedirectResponse
    {
        $updatesUrl->execute($url, $request->validated());

        return back()->with('success', __('panel::urls.flash_updated'));
    }

    public function destroy(Collection $collection, Url $url, DeletesUrl $deletesUrl): RedirectResponse
    {
        $deletesUrl->execute($url);

        return back()->with('success', __('panel::urls.flash_deleted'));
    }
}
