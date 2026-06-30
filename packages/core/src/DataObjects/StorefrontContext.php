<?php

namespace Lunar\Core\DataObjects;

use Illuminate\Support\Collection;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Region;

/**
 * The resolved storefront selections for a single operation — an explicit,
 * passable bundle that business logic can consume without an HTTP session.
 *
 * Produced by the session and the cart, or resolved directly by non-session
 * callers (API, jobs, tests). Immutable: the with* helpers return a fresh
 * instance with one selection changed. Deriving customer groups from a
 * customer is the resolver's job, not the value object's.
 */
final readonly class StorefrontContext
{
    /**
     * Channel and currency are store invariants (a cart cannot exist without
     * them). Language is optional — null falls back to the app locale until a
     * default language (or a region) supplies one. Region is the market this
     * operation belongs to and the provenance of the currency/language
     * defaults; null only when no region is configured.
     *
     * @param  Collection<int, CustomerGroup>  $customerGroups  the default group when no customer; empty only if none is configured
     */
    public function __construct(
        public Channel $channel,
        public Currency $currency,
        public ?Language $language,
        public ?Customer $customer,
        public Collection $customerGroups,
        public ?Region $region = null,
    ) {}

    public function withChannel(Channel $channel): self
    {
        return new self($channel, $this->currency, $this->language, $this->customer, $this->customerGroups, $this->region);
    }

    public function withCurrency(Currency $currency): self
    {
        return new self($this->channel, $currency, $this->language, $this->customer, $this->customerGroups, $this->region);
    }

    public function withLanguage(?Language $language): self
    {
        return new self($this->channel, $this->currency, $language, $this->customer, $this->customerGroups, $this->region);
    }

    public function withCustomer(?Customer $customer): self
    {
        return new self($this->channel, $this->currency, $this->language, $customer, $this->customerGroups, $this->region);
    }

    /**
     * @param  Collection<int, CustomerGroup>  $customerGroups
     */
    public function withCustomerGroups(Collection $customerGroups): self
    {
        return new self($this->channel, $this->currency, $this->language, $this->customer, $customerGroups, $this->region);
    }

    public function withRegion(?Region $region): self
    {
        return new self($this->channel, $this->currency, $this->language, $this->customer, $this->customerGroups, $region);
    }

    /**
     * Whether the storefront shows prices inclusive of tax for this context,
     * from the region's display preference (global storage default if none).
     */
    public function displaysPricesIncludingTax(): bool
    {
        return $this->region?->displaysPricesIncludingTax() ?? prices_inc_tax();
    }
}
