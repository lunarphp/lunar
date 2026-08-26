<?php

namespace Lunar\Core\Managers;

use Illuminate\Auth\AuthManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Collection;
use Lunar\Core\Contracts\Actions\Storefront\ResolvesStorefrontContext;
use Lunar\Core\Contracts\CartSession;
use Lunar\Core\Contracts\StorefrontSession;
use Lunar\Core\DataObjects\StorefrontContext;
use Lunar\Core\Exceptions\CustomerNotBelongsToUserException;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Region;

class StorefrontSessionManager implements StorefrontSession
{
    protected ?Region $region = null;

    protected ?Channel $channel = null;

    protected ?Collection $customerGroups = null;

    protected ?Currency $currency = null;

    protected ?Customer $customer = null;

    public function __construct(
        protected SessionManager $sessionManager,
        protected AuthManager $authManager,
        protected ResolvesStorefrontContext $resolveStorefrontContext,
        protected CartSession $cartSession,
    ) {
        $this->customerGroups = new Collection;

        $this->initRegion();
        $this->initChannel();
        $this->initCustomerGroups();
        $this->initCurrency();
        $this->initCustomer();
    }

    public function context(): StorefrontContext
    {
        return $this->resolveStorefrontContext->execute(
            channel: $this->getChannel(),
            currency: $this->getCurrency(),
            customer: $this->getCustomer(),
            customerGroups: $this->getCustomerGroups(),
            region: $this->getRegion(),
        );
    }

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(Region $region): static
    {
        $this->sessionManager->put(
            $this->getSessionKey().'_region',
            $region->handle,
        );

        $this->region = $region;

        // Channel and currency follow the region (a region belongs to a
        // channel). A later setChannel()/setCurrency() still overrides.
        $region->loadMissing(['channel', 'currency']);

        if ($region->channel) {
            $this->setChannel($region->channel);
        }

        if ($region->currency) {
            $this->setCurrency($region->currency);
        }

        return $this;
    }

    public function getChannel(): Channel
    {
        return $this->channel;
    }

    public function setChannel(Channel $channel): static
    {
        $this->sessionManager->put(
            $this->getSessionKey().'_channel',
            $channel->handle,
        );

        $this->channel = $channel;

        return $this;
    }

    /**
     * @return Collection<CustomerGroup>
     */
    public function getCustomerGroups(): Collection
    {
        return $this->customerGroups;
    }

    /**
     * @param  Collection<CustomerGroup>  $customerGroups
     */
    public function setCustomerGroups(Collection $customerGroups): static
    {
        $this->sessionManager->put(
            $this->getSessionKey().'_customer_groups',
            $customerGroups->pluck('handle')->toArray(),
        );

        $this->customerGroups = $customerGroups;

        return $this;
    }

    public function setCustomerGroup(CustomerGroup $customerGroup): static
    {
        return $this->setCustomerGroups(new Collection([$customerGroup]));
    }

    public function resetCustomerGroups(): static
    {
        $this->sessionManager->forget($this->getSessionKey().'_customer_groups');

        $this->customerGroups = new Collection;

        return $this;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function setCurrency(Currency $currency): static
    {
        $this->putCurrency($currency);

        // The cart carries its own currency_id and is priced from it, so a
        // storefront switch has to reach the cart or the shopper keeps seeing
        // the old currency. CartSession owns that write and the relation resets
        // it needs; it is a no-op when the visitor has no cart.
        $this->cartSession->setCurrency($currency);

        return $this;
    }

    /**
     * Store the currency without reaching for the cart.
     *
     * The boot cascade resolves a currency on every request, and must not
     * calculate — or create — a cart to do it.
     */
    protected function putCurrency(Currency $currency): void
    {
        $this->sessionManager->put(
            $this->getSessionKey().'_currency',
            $currency->code,
        );

        $this->currency = $currency;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(Customer $customer): static
    {
        $this->sessionManager->put(
            $this->getSessionKey().'_customer',
            $customer->id,
        );

        if (
            $this->authManager->check()
            && is_lunar_user($this->authManager->user())
            && ! $this->customerBelongsToUser($customer)
        ) {
            throw new CustomerNotBelongsToUserException;
        }

        $this->customer = $customer;

        return $this;
    }

    protected function customerBelongsToUser(Customer $customer): bool
    {
        $user = $this->authManager->user();

        return $customer->query()
            ->whereHas('users', fn (Builder $query): Builder => $query->where('user_id', $user->id))
            ->exists();
    }

    public function initRegion(): void
    {
        if ($this->region) {
            return;
        }

        $sessionRegion = $this->sessionManager->get($this->getSessionKey().'_region');

        // Restore the region directly (no propagation) so a persisted explicit
        // channel/currency override still wins in initChannel()/initCurrency().
        if ($sessionRegion && $region = Region::query()->where('handle', $sessionRegion)->first()) {
            $this->region = $region;

            return;
        }

        $this->region = Region::getDefault();
    }

    public function initChannel(): void
    {
        if ($this->channel) {
            return;
        }

        $sessionChannel = $this->sessionManager->get($this->getSessionKey().'_channel');

        if ($sessionChannel) {
            $channel = Channel::query()->where('handle', $sessionChannel)->firstOrFail();

            $this->setChannel($channel);

            return;
        }

        $this->setChannel($this->region?->channel ?? Channel::getDefault());
    }

    public function initCustomerGroups(): void
    {
        if ($this->customerGroups->isNotEmpty()) {
            return;
        }

        $sessionCustomerGroups = new Collection(
            $this->sessionManager->get(
                $this->getSessionKey().'_customer_groups'
            )
        );

        if ($sessionCustomerGroups->isNotEmpty()) {
            $customerGroups = CustomerGroup::query()->whereIn('handle', $sessionCustomerGroups)->get();

            $this->setCustomerGroups($customerGroups);

            return;
        }

        $this->setCustomerGroup(CustomerGroup::getDefault());
    }

    public function initCurrency(): void
    {
        if ($this->currency) {
            return;
        }

        $sessionCurrency = $this->sessionManager->get($this->getSessionKey().'_currency');

        if ($sessionCurrency) {
            $currency = Currency::query()->where('code', $sessionCurrency)->firstOrFail();

            $this->putCurrency($currency);

            return;
        }

        $this->putCurrency($this->region?->currency ?? Currency::getDefault());
    }

    public function initCustomer(): void
    {
        if ($this->customer) {
            return;
        }

        $sessionCustomer = $this->sessionManager->get($this->getSessionKey().'_customer');

        if ($sessionCustomer) {
            $customer = Customer::query()->findOrFail($sessionCustomer);

            $this->setCustomer($customer);

            return;
        }

        if ($this->authManager->check() && is_lunar_user($this->authManager->user())) {
            $user = $this->authManager->user();

            if ($customer = $user->latestCustomer()) {
                $this->setCustomer($customer);
            }
        }
    }

    public function forget(): void
    {
        $this->sessionManager->forget([
            $this->getSessionKey().'_region',
            $this->getSessionKey().'_channel',
            $this->getSessionKey().'_customer_groups',
            $this->getSessionKey().'_currency',
            $this->getSessionKey().'_customer',
        ]);
    }

    public function getSessionKey(): string
    {
        return 'lunar_storefront';
    }
}
