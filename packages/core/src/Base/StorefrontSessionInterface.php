<?php

namespace Lunar\Base;

use Illuminate\Support\Collection;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;

interface StorefrontSessionInterface
{
    /**
     * Return the session key for carts.
     *
     * @return string
     */
    public function getSessionKey();

    /**
     * Set the cart session channel.
     *
     * @param  \Lunar\Models\Channel  $channel
     * @return self
     */
    public function setChannel(Channel $channel);

    /**
     * Set the cart session currency.
     *
     * @param  \Lunar\Models\Currency  $currency
     * @return self
     */
    public function setCurrency(Currency $currency);

    /**
     * Set the store front session customer group
     *
     * @param Collection<CustomerGroup> $customerGroups
     *
     * @return void
     */
    public function setCustomerGroups(Collection $customerGroups);

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
}
