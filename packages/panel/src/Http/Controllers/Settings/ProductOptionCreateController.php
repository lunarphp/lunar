<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Lunar\Core\Contracts\Actions\ProductOptions\CreatesProductOption;
use Lunar\Panel\Http\Requests\Settings\ProductOptionRequest;

class ProductOptionCreateController
{
    public function store(ProductOptionRequest $request, CreatesProductOption $createsProductOption): RedirectResponse
    {
        $attributes = $request->productOptionAttributes();

        // The option's values are managed on the edit screen.
        unset($attributes['values']);

        // Settings is the shared-options area: anything created here is shared
        // by default (a dedicated option would never surface to be used). It can
        // be demoted later on the edit screen.
        $attributes['shared'] = true;

        $productOption = $createsProductOption->execute($attributes);

        return redirect()
            ->route('panel.settings.product-options.edit', $productOption)
            ->with('success', __('panel::product_options.flash_created'));
    }
}
