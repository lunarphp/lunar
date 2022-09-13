<?php

namespace Lunar\Hub\Http\Livewire\Pages\Orders;

use Livewire\Component;
use Lunar\Models\Order;

class OrderShow extends Component
{
    /**
     * The Product we are currently editing.
     *
     * @var \Lunar\Models\Product
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
