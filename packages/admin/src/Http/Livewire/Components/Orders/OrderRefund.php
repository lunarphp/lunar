<?php

namespace GetCandy\Hub\Http\Livewire\Components\Orders;

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
     * Confirm the refund.
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

    /**
     * The transaction id to refund.
     *
     * @var string|int
     */
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
     * @var bool
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
     * {@inheritDoc}
     */
    public function mount()
    {
        if ($this->charges->count() == 1) {
            $this->transaction = $this->charges->first()->id;
            $this->amount = $this->transactionModel->amount->value / 100;
        }

        $this->amount = $this->availableToRefund / 100;
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

    /**
     * Return the available charges.
     *
     * @return void
     */
    public function getChargesProperty()
    {
        return $this->order->transactions()->whereType('capture')->whereSuccess(true)->get();
    }

    /**
     * Return the existing refunds.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRefundsProperty()
    {
        return $this->order->transactions()->whereType('refund')->whereSuccess(true)->get();
    }

    /**
     * Return the amount that's available for refunding.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAvailableToRefundProperty()
    {
        return $this->charges->sum('amount.value') - $this->refunds->sum('amount.value');
    }

    /**
     * Return the amount that's available for refunding.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCanBeRefundedProperty()
    {
        return $this->availableToRefund > 0;
    }

    /**
     * Return the selected transaction model.
     *
     * @return void
     */
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

        if (! $response->success) {
            $this->emit('refundError', $this->transaction);

            $this->refundError = $response->message;

            $this->notify(
                message: 'There was a problem with the refund',
                level: 'error'
            );

            return;
        }

        $this->emit('refundSuccess', $this->transaction);

        $this->transaction = null;
        $this->amount = $this->availableToRefund / 100;
        $this->notes = '';
        $this->confirmed = false;

        $this->notify(
            message: 'Refund successful',
        );
    }

    /**
     * Cancel the refund.
     *
     * @return void
     */
    public function cancel()
    {
        $this->transaction = null;
        $this->amount = $this->availableToRefund / 100;
        $this->notes = '';
        $this->confirmed = false;

        $this->emit('cancelRefund');
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
