<?php

namespace Lunar\Panel\Http\Controllers\Products;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\Products\CreatesProduct;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductType;
use Lunar\Panel\Forms\FormSlices;
use Lunar\Panel\Http\Requests\Products\ProductStoreRequest;

class ProductCreateController
{
    public function create(FormSlices $formSlices): Response
    {
        return Inertia::render('products/Create', [
            'formSliceValues' => $formSlices->values(new Product) ?: (object) [],
            // Draft types are hidden from the create flow (spec 0056).
            'typeOptions' => ProductType::query()->active()->orderBy('name')->get(['id', 'name'])
                ->map(fn (ProductType $type) => ['value' => $type->id, 'label' => $type->name]),
            'urls' => [
                'store' => route('panel.products.store'),
                'index' => route('panel.products.index'),
            ],
        ]);
    }

    public function store(ProductStoreRequest $request, CreatesProduct $createsProduct, FormSlices $formSlices): RedirectResponse
    {
        $validated = $request->validated();

        $product = $formSlices->save($validated, fn () => $createsProduct->execute([
            'name' => [Language::getDefault()->code => $validated['name']],
            'product_type_id' => $validated['product_type_id'],
            'status' => $validated['status'] ?? 'draft',
        ]));

        return redirect()
            ->route('panel.products.edit', $product)
            ->with('success', __('panel::products.flash_created'));
    }
}
