<?php

namespace Lunar\Core\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A campaign that groups several discounts under one identity and window.
 * Promotions group and bound; the discounts beneath them still do the work.
 */
interface Promotion
{
    /**
     * The discounts belonging to this campaign.
     */
    public function discounts(): HasMany;
}
