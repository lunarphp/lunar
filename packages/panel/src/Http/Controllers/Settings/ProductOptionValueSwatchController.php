<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Lunar\Core\Contracts\Actions\Media\AddsMedia;
use Lunar\Core\Models\ProductOption;
use Lunar\Core\Models\ProductOptionValue;
use Lunar\Panel\Http\Requests\Settings\ProductOptionSwatchRequest;

/**
 * A swatch value carries a single image. Uploading replaces whatever was there,
 * so the collection is cleared first and only ever holds one file.
 */
class ProductOptionValueSwatchController
{
    public function store(ProductOptionSwatchRequest $request, ProductOption $productOption, ProductOptionValue $value, AddsMedia $addsMedia): RedirectResponse
    {
        $collection = config('lunar.media.collection');

        $value->clearMediaCollection($collection);
        $addsMedia->execute($value, $request->file('file'), $collection);

        return back()->with('success', __('panel::product_options.swatch_uploaded'));
    }

    public function destroy(ProductOption $productOption, ProductOptionValue $value): RedirectResponse
    {
        $value->clearMediaCollection(config('lunar.media.collection'));

        return back()->with('success', __('panel::product_options.swatch_removed'));
    }
}
