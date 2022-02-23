<?php

namespace GetCandy\Hub\Http\Livewire\Components\Orders;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Models\Order;
use Livewire\Component;

class OrderShow extends Component
{
    use Notifies;

    /**
     * The current order in view.
     *
     * @var \GetCandy\Models\Order
     */
    public Order $order;

    /**
     * Whether we are updating the status.
     *
     * @var bool
     */
    public bool $updatingStatus = false;

    /**
     * Whether all lines should be visible.
     *
     * @var boolean
     */
    public bool $allLinesVisible = false;

    /**
     * The maximum lines to show on load.
     *
     * @var integer
     */
    public int $maxLines = 10;

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            'order.status' => 'string',
        ];
    }

    /**
     * Return the computed status property.
     *
     * @return string
     */
    public function getStatusProperty()
    {
        return $this->statuses[$this->order->status] ?? $this->order->status;
    }

    /**
     * Return the configured statuses.
     *
     * @return array
     */
    public function getStatusesProperty()
    {
        return config('getcandy.orders.statuses');
    }

    /**
     * Get the billing address computed property.
     *
     * @return \GetCandy\Models\OrderAddress|null
     */
    public function getBillingProperty()
    {
        return $this->order->billingAddress;
    }

    /**
     * Get the shipping address computed property.
     *
     * @return \GetCandy\Models\OrderAddress|null
     */
    public function getShippingProperty()
    {
        return $this->order->shippingAddress;
    }

    /**
     * Return the computed shipping lines.
     *
     * @return void
     */
    public function getShippingLinesProperty()
    {
        return $this->order->shippingLines;
    }

    public function getVisibleLinesProperty()
    {
        return $this->physicalLines->take($this->allLinesVisible ? null : $this->maxLines);
    }

    public function getPhysicalLinesProperty()
    {
        return $this->order->lines->filter(function ($line) {
            return $line->type == 'physical';
        });
    }

    public function saveStatus()
    {
        $this->order->update([
            'status' => $this->order->status,
        ]);

        $this->notify('Order status updated');
        $this->updatingStatus = false;
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.orders.show')
            ->layout('adminhub::layouts.base');
    }
}
