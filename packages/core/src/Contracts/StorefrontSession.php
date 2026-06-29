<?php

namespace Lunar\Core\Contracts;

use Illuminate\Support\Collection;
use Lunar\Core\DataObjects\StorefrontContext;
use Lunar\Core\Models\Contracts\Channel;
use Lunar\Core\Models\Contracts\Currency;
use Lunar\Core\Models\Contracts\Customer;
use Lunar\Core\Models\Contracts\CustomerGroup;
use Lunar\Core\Models\Contracts\Region;

interface StorefrontSession
{
    /**
     * Produce a context from the session's resolved selections, for handing
     * to business logic that should not reach into the session itself.
     */
    public function context(): StorefrontContext;

    public function getRegion(): ?Region;

    public function setRegion(Region $region): static;

    public function getChannel(): Channel;

    public function setChannel(Channel $channel): static;

    public function getCustomerGroups(): ?Collection;

    public function setCustomerGroups(Collection $customerGroups): static;

    public function setCustomerGroup(CustomerGroup $customerGroup): static;

    public function getCurrency(): Currency;

    public function setCurrency(Currency $currency): static;

    public function getCustomer(): ?Customer;

    public function setCustomer(Customer $customer): static;

    public function getSessionKey(): string;
}
