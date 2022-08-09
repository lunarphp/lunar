<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Products;

use GetCandy\Models\Product;

class ProductShow extends AbstractProduct
{
    public Product $product;

    protected static string $view = 'products.show';

    /**
     * Called when the component is mounted.
     *
     * @return void
     */
    public function mount()
    {
        $this->options = collect();
        $this->variantsEnabled = $this->getVariantsCount() > 1;
        $this->variant = $this->product->variants->first();

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
     * Delete the product.
     *
     * @return void
     */
    public function delete()
    {
        $this->product->delete();
        $this->notify(
            __('adminhub::notifications.products.deleted'),
            'hub.products.index'
        );
    }

    protected function getSlotContexts()
    {
        return ['product.all', 'product.show'];
    }
}
