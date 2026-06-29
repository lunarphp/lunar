<?php

namespace Lunar\Core\Enums;

/**
 * The kind of physical stock movement recorded in the `on_hand` ledger.
 *
 * Deliberately a small set — the movement's `source` morph and `note` carry the
 * specifics. `on_hand` is the only bucket with an immutable ledger; `committed`,
 * `reserved` and `unavailable` are maintained counters, reconstructable from
 * their own sources, so they are not movement types here.
 */
enum StockMovementType: string
{
    /** The starting balance a level is seeded with (migration / backfill). */
    case OpeningBalance = 'opening_balance';

    /** Stock arriving into a location (goods-in). */
    case Received = 'received';

    /** Stock leaving a location on a fulfilment. */
    case Shipped = 'shipped';

    /** Stock coming back into a location (return / refund-restock). */
    case Returned = 'returned';

    /** A manual or corrective change that fits none of the above. */
    case Adjustment = 'adjustment';
}
