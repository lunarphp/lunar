<?php

namespace Lunar\Panel\Http\Controllers\Products;

use Illuminate\Http\RedirectResponse;
use Lunar\Core\Contracts\Actions\Products\GeneratesProductVariants;
use Lunar\Core\Exceptions\ProductActionException;
use Lunar\Core\Models\Product;
use Lunar\Panel\Http\Requests\Products\ProductOptionsGenerateRequest;

class ProductOptionsController
{
    /**
     * Sync the product's option selection and rebuild its variant set. An
     * empty selection is the collapse path back to a simple product. The
     * action recomputes the keep/add/remove diff authoritatively and refuses
     * outright while a removal carries order history.
     */
    public function generate(ProductOptionsGenerateRequest $request, Product $product, GeneratesProductVariants $generatesProductVariants): RedirectResponse
    {
        try {
            $result = $generatesProductVariants->execute($product, $request->selections());
        } catch (ProductActionException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', __('panel::products.flash_variants_generated', [
            'kept' => $result['kept'],
            'added' => $result['added'],
            'removed' => $result['removed'],
        ]));
    }
}
