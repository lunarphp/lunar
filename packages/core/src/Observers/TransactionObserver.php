<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Contracts\Actions\Orders\RecomputesOrderStatus;
use Lunar\Core\Models\Transaction;

class TransactionObserver
{
    public function __construct(
        protected RecomputesOrderStatus $recomputeOrderStatus,
    ) {}

    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        /** @var Transaction $transaction */
        activity()
            ->causedBy(auth()->user())
            ->performedOn($transaction->order()->first())
            ->event($transaction->type)
            ->withProperties([
                'amount' => $transaction->amount,
                'type' => $transaction->type,
                'status' => $transaction->status,
                'card_type' => $transaction->card_type,
                'last_four' => $transaction->last_four,
                'reference' => $transaction->reference,
                'notes' => $transaction->notes ?: '',
            ])->log('created');

        $this->recompute($transaction);
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        $this->recompute($transaction);
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        $this->recompute($transaction);
    }

    /**
     * Recompute the parent order's derived statuses from the ledger.
     */
    protected function recompute(Transaction $transaction): void
    {
        /** @var Transaction $transaction */
        if ($order = $transaction->order()->first()) {
            $this->recomputeOrderStatus->execute($order);
        }
    }
}
