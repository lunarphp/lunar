<?php

namespace Lunar\Core\States\Order\Payment;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/**
 * Derived payment status for an order — a pure rollup of the transaction
 * ledger (see `Actions\Orders\ResolvePaymentStatus`).
 *
 * This machine is **not** transition-guarded: the value is recomputed and
 * assigned directly by the resolver whenever the ledger changes, so any
 * value may follow any other. All states are registered so the cast resolves
 * regardless of which one the resolver produces.
 *
 * Named a `*Status` (not `*State`) by convention: a derived rollup is a
 * `*Status` and matches its `*_status` column, while a hand-driven, guarded
 * lifecycle machine is a `*State` (cf. the per-parcel `Fulfilment\FulfilmentState`).
 */
abstract class PaymentStatus extends State
{
    abstract public function label(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Pending::class)
            ->registerState([
                Pending::class,
                Authorized::class,
                PartiallyPaid::class,
                Paid::class,
                PartiallyRefunded::class,
                Refunded::class,
                Voided::class,
            ]);
    }
}
