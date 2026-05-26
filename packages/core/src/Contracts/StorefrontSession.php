<?php

namespace Lunar\Core\Contracts;

use Illuminate\Support\Collection;
use Lunar\Core\Models\Contracts\Channel;
use Lunar\Core\Models\Contracts\Currency;
use Lunar\Core\Models\Contracts\Customer;
use Lunar\Core\Models\Contracts\CustomerGroup;

interface StorefrontSession
{
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
