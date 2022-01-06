<?php

namespace GetCandy\Hub\Http\Livewire\Components\Products\ProductTypes;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Models\Attribute;
use GetCandy\Models\ProductType;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

abstract class AbstractProductType extends Component
{
    use Notifies;
    use WithPagination;

    /**
     * Instance of the parent product.
     *
     * @var \GetCandy\Models\ProductType
     */
    public ProductType $productType;

    /**
     * Attributes which are ready to be synced.
     *
     * @var \Illuminate\Support\Collection
     */
    public Collection $selectedAttributes;

    /**
     * The attribute search term.
     *
     * @var string
     */
    public $attributeSearch = '';

    public function addAttribute($id)
    {
        $this->selectedAttributes = $this->selectedAttributes->push(
            $this->availableAttributes()->first(fn ($att) => $att->id == $id)
        );
    }

    public function removeAttribute($id)
    {
        $index = $this->selectedAttributes->search(fn ($att) => $att->id == $id);

        $this->selectedAttributes->forget($index);
    }

    public function updatedAttributeSearch()
    {
        $this->resetPage();
    }

    public function availableAttributes()
    {
        // \Log::debug($this->selectedAttributes->pluck('id'));
        return Attribute::whereAttributeType(ProductType::class)
            ->when($this->attributeSearch, fn ($query, $search) => $query->where('name->en', 'LIKE', '%'.$search.'%'))
            ->whereSystem(false)
            ->whereNotIn('id', $this->selectedAttributes->pluck('id'))
            ->paginate(25);
    }
}
