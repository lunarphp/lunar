<?php

namespace Lunar\Hub\Http\Livewire\Components\Products\ProductTypes;

use Lunar\Models\Attribute;
use Lunar\Models\Product;
use Lunar\Models\ProductType;
use Lunar\Models\ProductVariant;

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
     * @return array
     */
    protected function rules()
    {
        return [
            'productType.name' => 'required|string|unique:'.get_class($this->productType).',name',
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

        $this->productType->attributables()->sync(
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
