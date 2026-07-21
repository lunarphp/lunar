<?php

namespace Lunar\Panel\Http\Controllers\ProductTypes;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\ProductTypes\CreatesProductType;
use Lunar\Panel\Http\Requests\ProductTypes\ProductTypeRequest;

class ProductTypeCreateController
{
    public function create(): Response
    {
        return Inertia::render('product-types/Create', [
            'urls' => [
                'store' => route('panel.product-types.store'),
                'index' => route('panel.product-types.index'),
            ],
        ]);
    }

    public function store(ProductTypeRequest $request, CreatesProductType $createsProductType): RedirectResponse
    {
        $productType = $createsProductType->execute(
            $request->productTypeAttributes(),
            $request->attributeMappingIds(null),
        );

        return redirect()
            ->route('panel.product-types.edit', $productType)
            ->with('success', __('panel::product-types.flash_created'));
    }
}
