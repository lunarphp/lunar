<?php

namespace Lunar\Base;

use Illuminate\Support\Collection;
use Lunar\Models\Contracts\Channel;
use Lunar\Models\Contracts\Currency;
use Lunar\Models\Contracts\Customer;
use Lunar\Models\Contracts\CustomerGroup;

interface StorefrontSessionInterface
{
    /**
     * Return the session key for carts.
     */
    public function getSessionKey(): string;

    /**
     * Set the cart session channel.
     */
    public function setChannel(Channel $channel): self;

    /**
     * Set the cart session currency.
     */
    public function setCurrency(Currency $currency): self;

    /**
     * Set the store front session customer group
     *
     * @param  Collection<CustomerGroup>  $customerGroups
     * @return void
     */
    public function setCustomerGroups(Collection $customerGroups): self;

    /**
     * Set the Customer Group
     */
    public function setCustomerGroup(CustomerGroup $customerGroup): self;

    /**
     * Return the current currency.
     */
    public function getCurrency(): Currency;

    /**
     * Return the current channel.
     */
    public function getChannel(): Channel;

    /**
     * Return the current customer groups
     */
    public function getCustomerGroups(): ?Collection;

    /**
     * Set the session customer.
     */
    public function setCustomer(Customer $customer): self;

    /**
     * Return the current customer.
     */
    public function getCustomer(): ?Customer;
}
