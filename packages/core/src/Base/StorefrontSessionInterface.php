<?php

namespace Lunar\Base;

use Illuminate\Support\Collection;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;

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
     *
     * @return Collection
     */
    public function getCustomerGroups(): ?Collection;

    /**
     * Set the session customer.
     *
     * @return self
     */
    public function setCustomer(Customer $customer): self;

    /**
     * Return the current customer.
     *
     * @return \Lunar\Models\Customer | null
     */
    public function getCustomer(): ?Customer;
}
