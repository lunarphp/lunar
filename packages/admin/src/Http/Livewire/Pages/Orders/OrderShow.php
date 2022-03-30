<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Orders;

use GetCandy\Models\Order;
use Livewire\Component;

class OrderShow extends Component
{
    /**
     * The Product we are currently editing.
     *
     * @var \GetCandy\Models\Product
     */
    public Order $order;

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.orders.show')
            ->layout('adminhub::layouts.app', [
                'title' => __('adminhub::orders.show.title', ['id' => $this->order->id]),
            ]);
    }
}
