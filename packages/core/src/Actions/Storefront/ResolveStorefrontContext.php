<?php

namespace Lunar\Core\Actions\Storefront;

use Illuminate\Support\Collection;
use Lunar\Core\Contracts\Actions\Storefront\ResolvesStorefrontContext;
use Lunar\Core\DataObjects\StorefrontContext;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Region;

/**
 * The single home for the storefront default cascade. Region falls back to
 * the default region; channel/currency/language then cascade explicit
 * override -> region default -> global default.
 */
class ResolveStorefrontContext implements ResolvesStorefrontContext
{
    public function execute(
        ?Channel $channel = null,
        ?Currency $currency = null,
        ?Language $language = null,
        ?Customer $customer = null,
        ?Collection $customerGroups = null,
        ?Region $region = null,
    ): StorefrontContext {
        $region ??= Region::getDefault();
        $region?->loadMissing(['channel', 'currency', 'language']);

        return new StorefrontContext(
            channel: $channel ?? $region?->channel ?? Channel::getDefault(),
            currency: $currency ?? $region?->currency ?? Currency::getDefault(),
            language: $language ?? $region?->language ?? Language::getDefault(),
            customer: $customer,
            customerGroups: $customerGroups?->isNotEmpty()
                ? $customerGroups
                : $this->resolveCustomerGroups($customer),
            region: $region,
        );
    }

    /**
     * @return Collection<int, CustomerGroup>
     */
    protected function resolveCustomerGroups(?Customer $customer): Collection
    {
        $groups = $customer?->customerGroups()->get() ?? new Collection;

        if ($groups->isNotEmpty()) {
            return $groups;
        }

        $default = CustomerGroup::getDefault();

        return $default ? new Collection([$default]) : new Collection;
    }
}
