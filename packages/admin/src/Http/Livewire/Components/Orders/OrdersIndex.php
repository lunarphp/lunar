<?php

namespace GetCandy\Hub\Http\Livewire\Components\Orders;

use GetCandy\Hub\Facades\OrdersTable;
use GetCandy\Hub\Search\OrderSearch;
use GetCandy\Hub\Tables\Orders;
use GetCandy\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class OrdersIndex extends Component
{
    use WithPagination;

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
        'from' => null,
        'to' => null,
    ];

    /**
     * Define what to track in the query string.
     *
     * @var array
     */
    protected $queryString = ['search', 'filters'];

    public function mount()
    {
        if (!$this->filters['from']) {
            $this->filters['from'] = now()->startOfMonth()->format('Y-m-d');
        }

        if (!$this->filters['to']) {
            $this->filters['to'] = now()->endOfMonth()->format('Y-m-d');
        }
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
