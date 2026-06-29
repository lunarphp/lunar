<?php

namespace Lunar\Core\Actions\Storefront;

use Illuminate\Support\Collection;
use Lunar\Core\Contracts\Actions\Storefront\ResolvesStorefrontContext;
use Lunar\Core\DataObjects\StorefrontContext;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Contracts\Channel as ChannelContract;
use Lunar\Core\Models\Contracts\Currency as CurrencyContract;
use Lunar\Core\Models\Contracts\Customer as CustomerContract;
use Lunar\Core\Models\Contracts\Language as LanguageContract;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;

/**
 * The single home for the storefront default cascade: an explicit override
 * wins, otherwise the relevant getDefault() applies. When regions land, this
 * resolver is the one place extended to cascade through a region's defaults.
 */
class ResolveStorefrontContext implements ResolvesStorefrontContext
{
    public function execute(
        ?ChannelContract $channel = null,
        ?CurrencyContract $currency = null,
        ?LanguageContract $language = null,
        ?CustomerContract $customer = null,
    ): StorefrontContext {
        return new StorefrontContext(
            channel: $channel ?? Channel::getDefault(),
            currency: $currency ?? Currency::getDefault(),
            language: $language ?? Language::getDefault(),
            customer: $customer,
            customerGroups: $this->resolveCustomerGroups($customer),
        );
    }

    /**
     * @return Collection<int, CustomerGroup>
     */
    protected function resolveCustomerGroups(?CustomerContract $customer): Collection
    {
        $groups = $customer?->customerGroups()->get() ?? new Collection;

        if ($groups->isNotEmpty()) {
            return $groups;
        }

        $default = CustomerGroup::getDefault();

        return $default ? new Collection([$default]) : new Collection;
    }
}
