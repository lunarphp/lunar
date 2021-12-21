<?php

namespace GetCandy\Hub\Http\Livewire\Components\Orders;

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
    ];

    /**
     * Define what to track in the query string.
     *
     * @var array
     */
    protected $queryString = ['search', 'filters'];

    public function getAppliedFiltersProperty()
    {
        return collect($this->filters)->filter();
    }

    public function updatedSearch()
    {
        $this->setPage(1);
    }

    public function getOrdersProperty()
    {
        $query = Order::search($this->search);

        foreach ($this->appliedFilters as $attribute => $value) {
            $query->where($attribute, $value);
        }

        return tap(
            $query->paginate(50),
            function ($orders) {
                return $orders->load(['billingAddress']);
            }
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
