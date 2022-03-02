<?php

namespace GetCandy\Observers;

use GetCandy\Models\Order;
use GetCandy\Models\Transaction;

class TransactionObserver
{
    /**
     * Handle the OrderLine "updated" event.
     *
     * @param  \GetCandy\Models\OrderLine  $orderLine
     * @return void
     */
    public function created(Transaction $transaction)
    {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($transaction->order)
            ->event('transaction')
            ->withProperties([
                'amount' => $transaction->amount->value,
                'refund' => $transaction->refund,
                'status' => $transaction->status,
                'card_type' => $transaction->card_type,
                'last_four' => $transaction->last_four,
                'reference' => $transaction->reference,
            ])->log('created');
    }
}
