<?php

namespace Lunar\Hub\Http\Livewire\Components\Orders;

use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Models\Order;
use Lunar\Models\Transaction;
use Livewire\Component;

class OrderCapture extends Component
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
     * The transaction id to capture.
     *
     * @var string|int
     */
    public $transaction;

    /**
     * The instance of the order to capture.
     *
     * @var Order
     */
    public Order $order;

    /**
     * The capture error message.
     *
     * @var bool
     */
    public string $captureError = '';

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            'amount' => 'required|numeric|min:1',
            'confirmed' => 'required',
            'transaction' => 'required',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function mount()
    {
        if ($this->intents->count() == 1) {
            $this->transaction = $this->intents->first()->id;
        }
    }

    /**
     * Return the available charges.
     *
     * @return void
     */
    public function getIntentsProperty()
    {
        return $this->order->transactions()->whereType('intent')->whereSuccess(true)->get();
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
    public function capture()
    {
        $this->captureError = '';

        $this->validate();

        $response = $this->transactionModel->capture(
            $this->amount * 100
        );

        if (! $response->success) {
            $this->emit('captureError', $this->transaction);

            $this->captureError = $response->message;

            $this->notify(
                message: 'There was a problem with the capture',
                level: 'error'
            );

            return;
        }

        $this->transaction = null;
        $this->amount = 0;

        $this->emit('captureSuccess', $this->transaction);

        $this->notify(
            message: 'Capture successful',
        );
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.orders.capture')
            ->layout('adminhub::layouts.base');
    }
}
