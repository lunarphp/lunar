<?php

namespace GetCandy\Hub\Http\Livewire\Components\Products;

use GetCandy\Hub\Http\Livewire\Traits\SearchesProducts;
use GetCandy\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class ProductsIndex extends Component
{
    use WithPagination, SearchesProducts;

    public $selectPage = false;
    public $selectAll = false;
    public $selected = [];

    /**
     * The search term.
     *
     * @var string
     */
    public $search = '';

    /**
     * The search filters.
     *
     * @var array
     */
    public $filters = [
        'status' => null,
    ];

    /**
     * Define what to track in the query string.
     *
     * @var array
     */
    protected $queryString = ['search', 'filters'];

    public function updatedSelectPage($value)
    {
        $this->selected = $value
            ? $this->products->pluck('id')->map(fn ($id) => (string) $id)
            : [];
    }

    public function selectAll()
    {
        $this->selectAll = true;
    }

    public function getProductsProperty()
    {
        return tap(Product::search($this->search)->paginate(50), function ($products) {
            return $products->load(['thumbnail', 'productType']);
        });
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.products.index', [
            'products' => $this->products,
        ])->layout('adminhub::layouts.base');
    }
}
