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
     *
     * @return string
     */
    public function getSessionKey(): string;

    /**
     * Set the cart session channel.
     *
     * @param  \Lunar\Models\Channel  $channel
     * @return self
     */
    public function setChannel(Channel $channel): self;

    /**
     * Set the cart session currency.
     *
     * @param  \Lunar\Models\Currency  $currency
     * @return self
     */
    public function setCurrency(Currency $currency): self;

    /**
     * Set the store front session customer group
     *
     * @param Collection<CustomerGroup> $customerGroups
     *
     * @return void
     */
    public function setCustomerGroups(Collection $customerGroups): self;

    /**
     * Set the Customer Group
     *
     * @param CustomerGroup $customerGroup
     *
     * @return self
     */
    public function setCustomerGroup(CustomerGroup $customerGroup): self;

    /**
     * Return the current currency.
     *
     * @return \Lunar\Models\Currency
     */
    public function getCurrency(): Currency;

    /**
     * Return the current channel.
     *
     * @return \Lunar\Models\Channel
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
     * @param  \Lunar\Models\Customer  $customer
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
