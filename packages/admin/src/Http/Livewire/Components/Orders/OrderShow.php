<?php

namespace GetCandy\Hub\Http\Livewire\Components\Orders;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Models\Channel;
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
     * Whether all lines should be visible.
     *
     * @var bool
     */
    public bool $allLinesVisible = false;

    /**
     * The maximum lines to show on load.
     *
     * @var int
     */
    public int $maxLines = 5;

    /**
     * Wether shipping address equals billing address.
     *
     * @var bool
     */
    public bool $shippingEqualsBilling = false;

    /**
     * The new comment property.
     *
     * @var string
     */
    public string $comment = '';

    /**
     * Whether to show the update status modal.
     *
     * @var bool
     */
    public bool $showUpdateStatus = false;

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            'order.status' => 'string',
            'comment' => 'required|string',
        ];
    }

    public function mount(Order $order)
    {
        $this->shippingEqualsBilling = optional($this->billing)->postcode == optional($this->shipping)->postcode;
        // dd($this->activityLog->last()['items']->first());
    }

    /**
     * Return the computed channel property.
     *
     * @return string
     */
    public function getChannelProperty()
    {
        return Channel::findOrFail($this->order->channel_id)->name;
    }

    /**
     * Return the configured statuses.
     *
     * @return array
     */
    public function getStatusesProperty()
    {
        return config('getcandy.orders.statuses', []);
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

    /**
     * Update the order status.
     *
     * @return void
     */
    public function updateStatus()
    {
        $this->order->update([
            'status' => $this->order->status,
        ]);

        $this->notify('Order status updated');
        $this->showUpdateStatus = false;
    }

    /**
     * Returns the activity log for the order.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getActivityLogProperty()
    {
        return $this->order->activities()->orderBy('created_at', 'desc')->get()->groupBy(function ($log) {
            return $log->created_at->format('Y-m-d');
        })->map(function ($logs) {
            return [
                'date' => $logs->first()->created_at->startOfDay(),
                'items' => $logs,
            ];
        });
    }

    /**
     * Add a comment to the order.
     *
     * @return void
     */
    public function addComment()
    {
        activity()
            ->performedOn($this->order)
            ->causedBy(
                auth()->user()
            )
            ->event('comment')
            ->withProperties(['content' => $this->comment])
            ->log('comment');

        $this->notify('Comment added');

        $this->comment = '';
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
