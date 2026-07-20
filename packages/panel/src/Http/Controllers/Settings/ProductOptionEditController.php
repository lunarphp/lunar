<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\ProductOptions\DeletesProductOption;
use Lunar\Core\Contracts\Actions\ProductOptions\UpdatesProductOption;
use Lunar\Core\Exceptions\ProductOptionActionException;
use Lunar\Core\Models\ProductOption;
use Lunar\Core\Models\ProductOptionValue;
use Lunar\Panel\Http\Requests\Settings\ProductOptionRequest;

class ProductOptionEditController
{
    public function edit(ProductOption $productOption): Response
    {
        return Inertia::render('settings/product-options/Edit', [
            'productOption' => [
                'id' => $productOption->id,
                'name' => $productOption->translate('name'),
                'handle' => $productOption->handle,
                'shared' => $productOption->shared,
            ],
            'values' => $productOption->values()
                ->withCount('variants')
                ->orderBy('position')
                ->get()
                ->map(fn (ProductOptionValue $value) => [
                    'id' => $value->id,
                    'name' => $value->translate('name'),
                    'position' => $value->position,
                    // Values carried by variants cannot be removed.
                    'inUse' => (int) $value->getAttribute('variants_count') > 0,
                ]),
            'hasProducts' => $productOption->products()->exists(),
            'urls' => [
                'update' => route('panel.settings.product-options.update', $productOption),
                'destroy' => route('panel.settings.product-options.destroy', $productOption),
                'index' => route('panel.settings.product-options.index'),
            ],
        ]);
    }

    public function update(ProductOptionRequest $request, ProductOption $productOption, UpdatesProductOption $updatesProductOption): RedirectResponse
    {
        try {
            $updatesProductOption->execute($productOption, $request->productOptionAttributes());
        } catch (ProductOptionActionException) {
            return back()->with('error', __('panel::product_options.value_delete_blocked'));
        }

        return back()->with('success', __('panel::product_options.flash_updated'));
    }

    public function destroy(ProductOption $productOption, DeletesProductOption $deletesProductOption): RedirectResponse
    {
        try {
            $deletesProductOption->execute($productOption);
        } catch (ProductOptionActionException) {
            return back()->with('error', __('panel::product_options.delete_blocked'));
        }

        return redirect()->route('panel.settings.product-options.index')->with('success', __('panel::product_options.flash_deleted'));
    }
}
