<?php

namespace Lunar\Panel\Http\Controllers\Products;

use Illuminate\Http\RedirectResponse;
use Lunar\Core\Contracts\Actions\Urls\CreatesUrl;
use Lunar\Core\Contracts\Actions\Urls\DeletesUrl;
use Lunar\Core\Contracts\Actions\Urls\UpdatesUrl;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\Url;
use Lunar\Panel\Http\Requests\Products\ProductUrlRequest;

class ProductUrlController
{
    public function store(ProductUrlRequest $request, Product $product, CreatesUrl $createsUrl): RedirectResponse
    {
        $createsUrl->execute($product, $request->validated());

        return back()->with('success', __('panel::urls.flash_created'));
    }

    public function update(ProductUrlRequest $request, Product $product, Url $url, UpdatesUrl $updatesUrl): RedirectResponse
    {
        $updatesUrl->execute($url, $request->validated());

        return back()->with('success', __('panel::urls.flash_updated'));
    }

    public function destroy(Product $product, Url $url, DeletesUrl $deletesUrl): RedirectResponse
    {
        $deletesUrl->execute($url);

        return back()->with('success', __('panel::urls.flash_deleted'));
    }
}
