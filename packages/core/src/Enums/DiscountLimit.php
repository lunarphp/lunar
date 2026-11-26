<?php

namespace Lunar\Core\Enums;

/**
 * A usage limit on a discount, named by the column that holds it.
 */
enum DiscountLimit: string
{
    /** Uses across every customer. */
    case MaxUses = 'max_uses';

    /** Uses by one customer, identified by user or email. */
    case MaxUsesPerUser = 'max_uses_per_user';
}
