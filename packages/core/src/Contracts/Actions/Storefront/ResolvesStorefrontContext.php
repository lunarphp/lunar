<?php

namespace Lunar\Core\Contracts\Actions\Storefront;

use Lunar\Core\DataObjects\StorefrontContext;
use Lunar\Core\Models\Contracts\Channel;
use Lunar\Core\Models\Contracts\Currency;
use Lunar\Core\Models\Contracts\Customer;
use Lunar\Core\Models\Contracts\Language;

interface ResolvesStorefrontContext
{
    /**
     * Resolve a storefront context, falling back to defaults for anything
     * not supplied. Customer groups derive from the customer when present,
     * otherwise the default group.
     */
    public function execute(
        ?Channel $channel = null,
        ?Currency $currency = null,
        ?Language $language = null,
        ?Customer $customer = null,
    ): StorefrontContext;
}
