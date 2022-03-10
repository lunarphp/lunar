<?php

namespace GetCandy\Hub\Http\Livewire\Components\Products;

use GetCandy\Models\Product;
use GetCandy\Models\ProductType;
use GetCandy\Models\ProductVariant;
use GetCandy\Models\TaxClass;

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
            'status'          => 'draft',
            'product_type_id' => ProductType::first()->id,
        ]);

        $this->options = collect();
        $this->variantsEnabled = $this->getVariantsCount() > 1;
        $this->variant = new ProductVariant([
            'purchasable'   => 'always',
            'tax_class_id'  => TaxClass::getDefault()?->id,
            'shippable'     => true,
            'stock'         => 0,
            'unit_quantity' => 1,
            'backorder'     => 0,
        ]);

        $this->variantAttributes = $this->parseAttributes(
            $this->availableVariantAttributes,
            $this->variant->attribute_data,
            'variantAttributes',
        );

        $this->syncAvailability();
        $this->syncAssociations();
        $this->syncCollections();
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

    protected function getSlotContexts()
    {
        return ['product.all', 'product.create'];
    }
}
