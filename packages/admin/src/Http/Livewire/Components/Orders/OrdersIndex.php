<?php

namespace GetCandy\Hub\Http\Livewire\Components\Orders;

use GetCandy\Hub\Facades\OrdersTable;
use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Hub\Http\Livewire\Traits\WithSavedSearches;
use GetCandy\Hub\Search\OrderSearch;
use GetCandy\Hub\Tables\Orders;
use GetCandy\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class OrdersIndex extends Component
{
    use WithPagination, WithSavedSearches, Notifies;

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
    public $filters = [];

    /**
     * Define what to track in the query string.
     *
     * @var array
     */
    protected $queryString = [
        'search' => ['except' => ''],
        'filters',
    ];

    public function mount()
    {
        $this->filters = array_merge([
            'status' => null,
            'to' => null,
            'from' => null,
        ], $this->filters);
    }

    public function rules()
    {
        return array_merge([
            'filters.from' => 'nullable',
            'filters.to' => 'nullable',
        ], $this->withSavedSearchesValidationRules());
    }

    public function getAppliedFiltersProperty()
    {
        return collect($this->filters)->filter();
    }

    public function updatedSearch()
    {
        $this->setPage(1);
    }

    public function getColumnsProperty()
    {
        return OrdersTable::getColumns();
    }

    public function getAvailableFiltersProperty()
    {
        return OrdersTable::getFilters();
    }

    public function getOrdersProperty()
    {
        $search = new OrderSearch();

        $filters = $this->filters;

        return $search->search(
            $this->search,
            [
                'filters' => $this->filters,
            ]
        );
    }

    public function getStatusesProperty()
    {
        return config('getcandy.orders.statuses');
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.orders.index')
            ->layout('adminhub::layouts.base');
    }
}
