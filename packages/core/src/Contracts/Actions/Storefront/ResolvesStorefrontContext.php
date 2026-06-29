<?php

namespace Lunar\Core\Contracts\Actions\Storefront;

use Illuminate\Support\Collection;
use Lunar\Core\DataObjects\StorefrontContext;
use Lunar\Core\Models\Contracts\Channel;
use Lunar\Core\Models\Contracts\Currency;
use Lunar\Core\Models\Contracts\Customer;
use Lunar\Core\Models\Contracts\CustomerGroup;
use Lunar\Core\Models\Contracts\Language;

interface ResolvesStorefrontContext
{
    /**
     * Resolve a storefront context, falling back to defaults for anything
     * not supplied. Supplied customer groups are used as-is; otherwise they
     * derive from the customer, falling back to the default group.
     *
     * @param  Collection<int, CustomerGroup>|null  $customerGroups
     */
    public function execute(
        ?Channel $channel = null,
        ?Currency $currency = null,
        ?Language $language = null,
        ?Customer $customer = null,
        ?Collection $customerGroups = null,
    ): StorefrontContext;
}
