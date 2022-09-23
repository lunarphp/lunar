<?php

namespace Lunar\Hub\Http\Livewire\Components\Products;

class ProductShow extends AbstractProduct
{
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

    /**
     * Restore the product.
     *
     * @return void
     */
    public function restore()
    {
        $this->product->restore();
        $this->showRestoreConfirm = false;
        $this->notify(
            __('adminhub::notifications.products.product_restored')
        );
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // dd($this->attributeData);
        return view('adminhub::livewire.components.products.show')->layout('adminhub::layouts.base');
    }

    protected function getSlotContexts()
    {
        return ['product.all', 'product.show'];
    }
}
