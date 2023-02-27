<?php

namespace Lunar\Observers;

use Lunar\Models\Transaction;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     *
     * @param  \Lunar\Models\Transaction  $orderLine
     * @return void
     */
    public function created(Transaction $transaction)
    {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($transaction->order)
            ->event($transaction->type)
            ->withProperties([
                'amount' => $transaction->amount->value,
                'type' => $transaction->type,
                'status' => $transaction->status,
                'card_type' => $transaction->card_type,
                'last_four' => $transaction->last_four,
                'reference' => $transaction->reference,
                'notes' => $transaction->notes ?: '',
            ])->log('created');
    }
}
