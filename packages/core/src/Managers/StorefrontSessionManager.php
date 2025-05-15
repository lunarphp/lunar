<?php

namespace Lunar\Managers;

use Illuminate\Auth\AuthManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Collection;
use Lunar\Base\StorefrontSessionInterface;
use Lunar\Exceptions\CustomerNotBelongsToUserException;
use Lunar\Models\Channel;
use Lunar\Models\Contracts\Channel as ChannelContract;
use Lunar\Models\Contracts\Currency as CurrencyContract;
use Lunar\Models\Contracts\Customer as CustomerContract;
use Lunar\Models\Contracts\CustomerGroup as CustomerGroupContract;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;

class StorefrontSessionManager implements StorefrontSessionInterface
{
    protected ?ChannelContract $channel = null;

    protected ?Collection $customerGroups = null;

    protected ?CurrencyContract $currency = null;

    protected ?CustomerContract $customer = null;

    public function __construct(
        protected SessionManager $sessionManager,
        protected AuthManager $authManager,
    ) {
        $this->customerGroups = new Collection;

        $this->initChannel();
        $this->initCustomerGroups();
        $this->initCurrency();
        $this->initCustomer();
    }

    public function getChannel(): ChannelContract
    {
        return $this->channel;
    }

    public function setChannel(ChannelContract $channel): static
    {
        $this->sessionManager->put(
            $this->getSessionKey().'_channel',
            $channel->handle,
        );

        $this->channel = $channel;

        return $this;
    }

    /**
     * @return \Illuminate\Support\Collection<\Lunar\Models\Contracts\CustomerGroup>
     */
    public function getCustomerGroups(): Collection
    {
        return $this->customerGroups;
    }

    /**
     * @param  \Illuminate\Support\Collection<\Lunar\Models\Contracts\CustomerGroup>  $customerGroups
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

    public function setCustomerGroup(CustomerGroupContract $customerGroup): static
    {
        return $this->setCustomerGroups(new Collection([$customerGroup]));
    }

    public function resetCustomerGroups(): static
    {
        $this->sessionManager->forget($this->getSessionKey().'_customer_groups');

        $this->customerGroups = new Collection;

        return $this;
    }

    public function getCurrency(): CurrencyContract
    {
        return $this->currency;
    }

    public function setCurrency(CurrencyContract $currency): static
    {
        $this->sessionManager->put(
            $this->getSessionKey().'_currency',
            $currency->code,
        );

        $this->currency = $currency;

        return $this;
    }

    public function getCustomer(): ?CustomerContract
    {
        return $this->customer;
    }

    public function setCustomer(CustomerContract $customer): static
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

    protected function customerBelongsToUser(CustomerContract $customer): bool
    {
        $user = $this->authManager->user();

        return $customer->query()
            ->whereHas('users', fn (Builder $query): Builder => $query->where('user_id', $user->id))
            ->exists();
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

        $this->setChannel(Channel::getDefault());
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

            $this->setCurrency($currency);

            return;
        }

        $this->setCurrency(Currency::getDefault());
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
