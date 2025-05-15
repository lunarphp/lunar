<?php

namespace Lunar\Base;

use Illuminate\Support\Collection;
use Lunar\Models\Contracts\Channel;
use Lunar\Models\Contracts\Currency;
use Lunar\Models\Contracts\Customer;
use Lunar\Models\Contracts\CustomerGroup;

interface StorefrontSessionInterface
{
    public function getSessionKey(): string;

    public function setChannel(Channel $channel): self;

    public function setCurrency(Currency $currency): self;

    public function setCustomerGroups(Collection $customerGroups): self;

    public function setCustomerGroup(CustomerGroup $customerGroup): self;

    public function getCurrency(): Currency;

    public function getChannel(): Channel;

    public function getCustomerGroups(): ?Collection;

    public function setCustomer(Customer $customer): self;

    public function getCustomer(): ?Customer;
}
