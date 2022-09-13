<?php

namespace Lunar\Hub\Http\Livewire\Components\Products\Variants\Editing;

use Lunar\Models\ProductVariant;
use Livewire\Component;

class Inventory extends Component
{
    public ProductVariant $variant;

    protected function rules()
    {
        $table = $this->variant->getTable();

        return [
            'variant.sku' => "required|string|unique:$table,sku,{$this->variant->id}|max:255",
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.products.variants.editing.inventory')
            ->layout('adminhub::layouts.base');
    }
}
