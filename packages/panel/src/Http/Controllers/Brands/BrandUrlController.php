<?php

namespace Lunar\Panel\Http\Controllers\Brands;

use Illuminate\Http\RedirectResponse;
use Lunar\Core\Contracts\Actions\Urls\CreatesUrl;
use Lunar\Core\Contracts\Actions\Urls\DeletesUrl;
use Lunar\Core\Contracts\Actions\Urls\UpdatesUrl;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Url;
use Lunar\Panel\Http\Requests\Brands\BrandUrlRequest;

class BrandUrlController
{
    public function store(BrandUrlRequest $request, Brand $brand, CreatesUrl $createsUrl): RedirectResponse
    {
        $createsUrl->execute($brand, $request->validated());

        return back()->with('success', __('panel::urls.flash_created'));
    }

    public function update(BrandUrlRequest $request, Brand $brand, Url $url, UpdatesUrl $updatesUrl): RedirectResponse
    {
        $updatesUrl->execute($url, $request->validated());

        return back()->with('success', __('panel::urls.flash_updated'));
    }

    public function destroy(Brand $brand, Url $url, DeletesUrl $deletesUrl): RedirectResponse
    {
        $deletesUrl->execute($url);

        return back()->with('success', __('panel::urls.flash_deleted'));
    }
}
