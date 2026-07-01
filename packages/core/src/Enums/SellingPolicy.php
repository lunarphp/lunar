<?php

namespace Lunar\Core\Enums;

/**
 * How a variant may be sold relative to its stock. Decides whether a quantity
 * can be fulfilled and what `getTotalInventory()` reports.
 */
enum SellingPolicy: string
{
    /** Sell regardless of stock; any quantity is fulfillable. */
    case Always = 'always';

    /** Sell only what is physically available. */
    case InStock = 'in_stock';

    /** Sell what is available plus the backorder allowance. */
    case InStockOrOnBackorder = 'in_stock_or_on_backorder';
}
