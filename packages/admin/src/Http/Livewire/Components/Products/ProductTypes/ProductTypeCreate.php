<?php

namespace GetCandy\Hub\Http\Livewire\Components\Products\ProductTypes;

use GetCandy\Models\Attribute;
use GetCandy\Models\Product;
use GetCandy\Models\ProductType;
use GetCandy\Models\ProductVariant;

class ProductTypeCreate extends AbstractProductType
{
    /**
     * Mount the component.
     *
     * @return void
     */
    public function mount()
    {
        $this->productType = new ProductType();

        $this->selectedProductAttributes = Attribute::system(Product::class)->get();
        $this->selectedVariantAttributes = Attribute::system(ProductVariant::class)->get();
    }

    /**
     * Register the validation rules.
     *
     * @return void
     */
    protected function rules()
    {
        return [
            'productType.name' => 'required|string|unique:'.$this->productType->getTable().',name',
        ];
    }

    /**
     * Method to handle product type saving.
     *
     * @return void
     */
    public function create()
    {
        $this->validate();

        $this->productType->save();

        $this->productType->mappedAttributes()->sync(
            array_merge(
                $this->selectedProductAttributes->pluck('id')->toArray(),
                $this->selectedVariantAttributes->pluck('id')->toArray()
            )
        );

        $this->notify(
            __('adminhub::catalogue.product-types.show.updated_message'),
            'hub.product-types.index'
        );
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.products.product-types.create')
            ->layout('adminhub::layouts.base');
    }
}
