<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Products\Variants;

use GetCandy\Models\Product;
use GetCandy\Models\ProductVariant;
use Livewire\Component;

class VariantShow extends Component
{
    /**
     * The current product.
     *
     * @var \GetCandy\Models\Product
     */
    public Product $product;

    /**
     * The current variant.
     *
     * @var \GetCandy\Models\ProductVariant
     */
    public ProductVariant $variant;

    /**
     * Save the variant.
     *
     * @return void
     */
    public function save()
    {
        $this->variant->save();
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.products.variants.show')
            ->layout('adminhub::layouts.app', [
                'title' => 'Edit Variant',
            ]);
    }
}
