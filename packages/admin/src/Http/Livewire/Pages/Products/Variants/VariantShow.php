<?php

namespace Lunar\Hub\Http\Livewire\Pages\Products\Variants;

use Livewire\Component;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

class VariantShow extends Component
{
    /**
     * The current product.
     */
    public Product $product;

    /**
     * The current variant.
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
