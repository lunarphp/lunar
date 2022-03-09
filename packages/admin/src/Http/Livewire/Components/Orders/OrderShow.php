<?php

namespace GetCandy\Hub\Http\Livewire\Components\Orders;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Hub\Http\Livewire\Traits\WithCountries;
use GetCandy\Models\Channel;
use GetCandy\Models\Order;
use GetCandy\Models\OrderAddress;
use Livewire\Component;

class OrderShow extends Component
{
    use Notifies, WithCountries;

    /**
     * The current order in view.
     *
     * @var \GetCandy\Models\Order
     */
    public Order $order;

    /**
     * The instance of the shipping address.
     *
     * @var \GetCandy\Models\OrderAddress
     */
    public ?OrderAddress $shippingAddress = null;

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
     * Whether to show the address edit screen.
     *
     * @var bool
     */
    public bool $showShippingAddressEdit = false;

    /**
     * The currently selected lines.
     *
     * @var array
     */
    public array $selectedLines = [];

    /**
     * Whether to show the refund panel.
     *
     * @var bool
     */
    public bool $showRefund = false;

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        $rules = [
            'order.status' => 'string',
            'comment' => 'required|string',
        ];

        if ($this->shippingAddress) {
            $rules = array_merge($rules, [
                'shippingAddress.postcode' => 'required|string|max:255',
                'shippingAddress.title' => 'nullable|string|max:255',
                'shippingAddress.first_name' => 'nullable|string|max:255',
                'shippingAddress.last_name' => 'nullable|string|max:255',
                'shippingAddress.company_name' => 'nullable|string|max:255',
                'shippingAddress.line_one' => 'nullable|string|max:255',
                'shippingAddress.line_two' => 'nullable|string|max:255',
                'shippingAddress.line_three' => 'nullable|string|max:255',
                'shippingAddress.city' => 'nullable|string|max:255',
                'shippingAddress.state' => 'nullable|string|max:255',
                'shippingAddress.delivery_instructions' => 'nullable|string|max:255',
                'shippingAddress.contact_email' => 'nullable|email|max:255',
                'shippingAddress.contact_phone' => 'nullable|string|max:255',
                'shippingAddress.country_id'   => 'required',
            ]);
        }

        return $rules;
    }

    public function mount()
    {
        $this->shippingAddress = $this->order->shippingAddress;
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
     * Return the computed shipping lines.
     *
     * @return void
     */
    public function getShippingLinesProperty()
    {
        return $this->order->shippingLines;
    }

    /**
     * Return all lines "above the fold".
     *
     * @return void
     */
    public function getVisibleLinesProperty()
    {
        return $this->physicalLines->take($this->allLinesVisible ? null : $this->maxLines);
    }

    /**
     * Return the physical order lines.
     *
     * @return void
     */
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
     * Handler when shipping edit toggle is updated.
     *
     * @return void
     */
    public function updatedShowShippingAddressEdit()
    {
        $this->shippingAddress = $this->shippingAddress->refresh();
    }

    /**
     * Return the refund amount based on selected lines
     * or based on the order total.
     *
     * @return int
     */
    public function getRefundAmountProperty()
    {
        if (count($this->selectedLines)) {
            return $this->order->lines->filter(function ($line) {
                return in_array($line->id, $this->selectedLines);
            })->sum('total.value');
        }

        return $this->order->total->value;
    }

    /**
     * Handle when selected order lines update.
     *
     * @param array $val
     * @return void
     */
    public function updatedSelectedLines($val)
    {
        $this->emit('updateRefundAmount', $this->refundAmount);
    }

    /**
     * Save the shipping address.
     *
     * @return void
     */
    public function saveShippingAddress()
    {
        $addressRules = collect($this->rules())->filter(function ($rule, $field) {
            return str_contains($field, 'shippingAddress');
        });

        $this->validate($addressRules->toArray());

        $this->shippingAddress->save();

        $this->notify('Shipping Address Saved');

        $this->showShippingAddressEdit = false;
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
     * Return whether the billing postcode matches the shipping postcode.
     *
     * @return void
     */
    public function getShippingEqualsBillingProperty()
    {
        return optional($this->billing)->postcode == optional($this->shippingAddress)->postcode;
    }

    /**
     * Display meta fields.
     *
     * @return void
     */
    public function getMetaFieldsProperty()
    {
        return (array) $this->order->meta;
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
