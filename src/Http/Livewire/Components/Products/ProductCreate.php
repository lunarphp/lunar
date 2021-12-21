<?php

namespace GetCandy\Hub\Http\Livewire\Components\Products;

use GetCandy\Models\Product;
use GetCandy\Models\ProductType;
use GetCandy\Models\ProductVariant;

class ProductCreate extends AbstractProduct
{
    /**
     * Called when the component is mounted.
     *
     * @return void
     */
    public function mount()
    {
        $this->product = new Product([
            'status' => 'draft',
            'product_type_id' => ProductType::first()->id,
        ]);

        $this->options = collect();
        $this->variantsEnabled = $this->getVariantsCount() > 1;
        $this->variant = new ProductVariant([
            'purchasable' => 'always',
            'shippable' => true,
            'stock' => 0,
            'backorder' => 0,
        ]);

        $this->syncAvailability();
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.products.create')
            ->layout('adminhub::layouts.base');
    }
}
