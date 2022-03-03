<?php

namespace GetCandy\Hub\Http\Livewire\Components\Orders;

use GetCandy\Models\Order;
use Livewire\Component;

class OrderRefund extends Component
{
    /**
     * The amount to refund
     *
     * @var integer
     */
    public $amount = 0;

    /**
     * The fail safe confirm text.
     *
     * @var string
     */
    public string $confirmText = '';

    /**
     * Any notes for the refund.
     *
     * @var string
     */
    public string $notes = '';

    /**
     * The instance of the order to refund.
     *
     * @var Order
     */
    public Order $order;

    /**
     * {@inheritDoc}
     *
     * @var array
     */
    protected $listeners = [
        'updateRefundAmount'
    ];

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            'amount' => 'required|numeric|min:1',
            'confirmText' => 'required',
            'notes' => 'nullable|string',
        ];
    }

    /**
     * Update the refund amount
     *
     * @param int $val
     * @return void
     */
    public function updateRefundAmount(int $val)
    {
        $this->amount = $val / 100;
    }

    /**
     * Whether the confirmText matches what is required to send the refund.
     *
     * @return void
     */
    public function getIsConfirmedProperty()
    {
        return $this->confirmText === __('adminhub::components.orders.refund.confirm_text');
    }

    /**
     * Action the refund.
     *
     * @return void
     */
    public function refund()
    {
        $this->validate();
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.orders.refund')
            ->layout('adminhub::layouts.base');
    }
}
