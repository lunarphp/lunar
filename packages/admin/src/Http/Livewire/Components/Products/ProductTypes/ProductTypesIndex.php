<?php

namespace GetCandy\Hub\Http\Livewire\Components\Products\ProductTypes;

use GetCandy\Models\ProductType;
use Livewire\Component;
use Livewire\WithPagination;

class ProductTypesIndex extends Component
{
    use WithPagination;

    public $selectPage = false;

    /**
     * Search term.
     *
     * @var string
     */
    public $search = '';

    /**
     * With properties to pass into query string.
     *
     * @var array
     */
    protected $queryString = ['search'];

    /**
     * Handle when search is updated.
     *
     * @param  string  $value
     * @return void
     */
    public function updatedSearch($value)
    {
        $this->resetPage();
    }

    /**
     * Get the product types to list.
     *
     * @return void
     */
    public function getProductTypesProperty()
    {
        return ProductType::withCount(['products', 'mappedAttributes'])
            ->when($this->search, fn ($query, $search) => $query->where('name', 'LIKE', '%'.$search.'%'))
            ->paginate(50);
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.product-types.index', [
            'productTypes' => $this->productTypes,
        ])->layout('adminhub::layouts.base');
    }
}
