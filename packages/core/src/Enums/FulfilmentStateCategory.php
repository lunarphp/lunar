<?php

namespace Lunar\Core\Enums;

/**
 * The fixed rollup vocabulary every per-fulfilment state belongs to.
 *
 * Per-method state graphs are open — a flow can add whatever intermediate
 * steps it needs — but every state declares one of these four categories, and
 * the order-level rollup (and the split / merge / return mechanics) reason only
 * in these terms. That single constraint is what keeps an arbitrary, registered
 * flow safe: it can extend the states, never the categories.
 */
enum FulfilmentStateCategory
{
    /** Not yet handed over: pending, in-progress, ready-for-collection, … */
    case Outstanding;

    /** Gone to the customer: shipped, collected, provisioned, … */
    case Fulfilled;

    /** Came back after being handed over. */
    case Returned;

    /** Never counted by the rollup. */
    case Cancelled;
}
