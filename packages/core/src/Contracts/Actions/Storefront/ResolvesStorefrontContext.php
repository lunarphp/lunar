<?php

namespace Lunar\Core\Contracts\Actions\Storefront;

use Illuminate\Support\Collection;
use Lunar\Core\DataObjects\StorefrontContext;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Region;

interface ResolvesStorefrontContext
{
    /**
     * Resolve a storefront context. Region falls back to the default region,
     * and channel/currency/language cascade explicit override -> region
     * default -> global default. Supplied customer groups are used as-is;
     * otherwise they derive from the customer, falling back to the default
     * group.
     *
     * @param  Collection<int, CustomerGroup>|null  $customerGroups
     */
    public function execute(
        ?Channel $channel = null,
        ?Currency $currency = null,
        ?Language $language = null,
        ?Customer $customer = null,
        ?Collection $customerGroups = null,
        ?Region $region = null,
    ): StorefrontContext;
}
