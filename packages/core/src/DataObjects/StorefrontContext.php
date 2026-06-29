<?php

namespace Lunar\Core\DataObjects;

use Illuminate\Support\Collection;
use Lunar\Core\Models\Contracts\Channel;
use Lunar\Core\Models\Contracts\Currency;
use Lunar\Core\Models\Contracts\Customer;
use Lunar\Core\Models\Contracts\CustomerGroup;
use Lunar\Core\Models\Contracts\Language;

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
     * @param  Collection<int, CustomerGroup>  $customerGroups  never empty; the default group when no customer
     */
    public function __construct(
        public Channel $channel,
        public Currency $currency,
        public Language $language,
        public ?Customer $customer,
        public Collection $customerGroups,
    ) {}

    public function withChannel(Channel $channel): self
    {
        return new self($channel, $this->currency, $this->language, $this->customer, $this->customerGroups);
    }

    public function withCurrency(Currency $currency): self
    {
        return new self($this->channel, $currency, $this->language, $this->customer, $this->customerGroups);
    }

    public function withLanguage(Language $language): self
    {
        return new self($this->channel, $this->currency, $language, $this->customer, $this->customerGroups);
    }

    public function withCustomer(?Customer $customer): self
    {
        return new self($this->channel, $this->currency, $this->language, $customer, $this->customerGroups);
    }

    /**
     * @param  Collection<int, CustomerGroup>  $customerGroups
     */
    public function withCustomerGroups(Collection $customerGroups): self
    {
        return new self($this->channel, $this->currency, $this->language, $this->customer, $customerGroups);
    }
}
