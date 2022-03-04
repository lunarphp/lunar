<?php

namespace GetCandy\Hub\Http\Livewire\Components\Orders;

use GetCandy\Base\DataTransferObjects\PaymentRefund;
use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Models\Order;
use GetCandy\Models\Transaction;
use Livewire\Component;

class OrderRefund extends Component
{
    use Notifies;

    /**
     * The amount to refund.
     *
     * @var int
     */
    public $amount = 0;

    /**
     * Confirm the refund
     *
     * @var string
     */
    public bool $confirmed = false;

    /**
     * Any notes for the refund.
     *
     * @var string
     */
    public string $notes = '';

    public $transaction;

    /**
     * The instance of the order to refund.
     *
     * @var Order
     */
    public Order $order;

    /**
     * The refund error message.
     *
     * @var boolean
     */
    public string $refundError = '';

    /**
     * {@inheritDoc}
     *
     * @var array
     */
    protected $listeners = [
        'updateRefundAmount',
    ];

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            'amount' => 'required|numeric|min:1',
            'confirmed' => 'required',
            'notes' => 'nullable|string',
            'transaction' => 'required',
        ];
    }

    /**
     * Update the refund amount.
     *
     * @param  int  $val
     * @return void
     */
    public function updateRefundAmount(int $val)
    {
        $this->amount = $val / 100;
    }

    public function getChargesProperty()
    {
        return $this->order->transactions()->whereRefund(false)->whereSuccess(true)->get();
    }

    public function getTransactionModelProperty()
    {
        return Transaction::find($this->transaction);
    }

    /**
     * Action the refund.
     *
     * @return void
     */
    public function refund()
    {
        $this->refundError = '';

        $this->validate();

        $response = $this->transactionModel->refund($this->amount * 100, $this->notes);

        if (!$response->success) {
            $this->emit('refundError', $this->transaction);

            $this->refundError = $response->message;

            $this->notify(
                message: 'There was a problem with the refund',
                level: 'error'
            );

            return;
        }

        $this->transaction = null;
        $this->amount = 0;
        $this->notes = '';

        $this->emit('refundSuccess', $this->transaction);

        $this->notify(
            message: 'Refund successful',
        );
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
