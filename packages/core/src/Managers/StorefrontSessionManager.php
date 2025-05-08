<?php

namespace Lunar\Managers;

use Illuminate\Auth\AuthManager;
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
    /**
     * The current channel
     */
    protected ?ChannelContract $channel = null;

    /**
     * The collection of customer groups to use.
     */
    protected ?Collection $customerGroups = null;

    /**
     * The current currency
     */
    protected ?CurrencyContract $currency = null;

    /**
     * The current customer
     */
    protected ?CustomerContract $customer = null;

    /**
     * Initialise the manager
     *
     * @param protected SessionManager
     */
    public function __construct(
        protected SessionManager $sessionManager,
        protected AuthManager $authManager
    ) {
        if (! $this->customerGroups) {
            $this->customerGroups = collect();
        }

        $this->initChannel();
        $this->initCustomerGroups();
        $this->initCustomer();
    }

    /**
     * {@inheritDoc}
     */
    public function forget()
    {
        $this->sessionManager->forget(
            $this->getSessionKey()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function initCustomerGroups()
    {
        $groupHandles = collect(
            $this->sessionManager->get(
                $this->getSessionKey().'_customer_groups'
            )
        );

        if ($this->customerGroups?->count()) {
            if ($groupHandles->isEmpty()) {
                return $this->setCustomerGroups(
                    $this->customerGroups
                );
            }

            return $this->customerGroups;
        }

        if (! $groupHandles->isEmpty()) {
            return $this->customerGroups = CustomerGroup::whereIn('handle', $groupHandles)->get();
        }

        return $this->setCustomerGroups(
            collect([
                CustomerGroup::getDefault(),
            ])
        );
    }

    /**
     * {@inheritDoc}
     */
    public function initChannel()
    {
        if ($this->channel) {
            return $this->channel;
        }

        $channelHandle = $this->sessionManager->get(
            $this->getSessionKey().'_channel'
        );

        if (! $channelHandle) {
            return $this->setChannel(
                Channel::getDefault()
            );
        }

        $channel = Channel::whereHandle($channelHandle)->first();

        if (! $channel) {
            throw new \Exception(
                "Unable to find channel with handle {$channelHandle}"
            );
        }

        return $this->setChannel($channel);
    }

    /**
     * {@inheritDoc}
     */
    public function initCustomer(): ?CustomerContract
    {
        if ($this->customer) {
            return $this->customer;
        }

        $customer_id = $this->sessionManager->get(
            $this->getSessionKey().'_customer'
        );

        if (! $customer_id) {
            if ($this->authManager->check() && is_lunar_user($this->authManager->user())) {
                $user = $this->authManager->user();

                if ($customer = $user->latestCustomer()) {
                    $this->setCustomer($customer);

                    return $this->customer;
                }
            }

            return null;
        }

        $customer = Customer::find($customer_id);

        if (! $customer) {
            return null;
        }

        $this->setCustomer($customer);

        return $this->customer;
    }

    /**
     * {@inheritDoc}
     */
    public function getSessionKey(): string
    {
        return 'lunar_storefront';
    }

    /**
     * {@inheritDoc}
     */
    public function setChannel(ChannelContract|string $channel): self
    {
        $this->sessionManager->put(
            $this->getSessionKey().'_channel',
            $channel->handle
        );
        $this->channel = $channel;

        return $this;
    }

    private function customerBelongsToUser(CustomerContract $customer): bool
    {
        /** @var Customer $customer */
        $user = $this->authManager->user();

        return $customer->query()
            ->whereHas('users', fn ($query) => $query->where('user_id', $user->id))
            ->exists();
    }

    /**
     * {@inheritDoc}
     */
    public function setCustomer(CustomerContract $customer): self
    {
        /** @var Customer $customer */
        $this->sessionManager->put(
            $this->getSessionKey().'_customer',
            $customer->id
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

    /**
     * {@inheritDoc}
     */
    public function getCustomer(): ?CustomerContract
    {
        return $this->customer ?: $this->initCustomer();
    }

    /**
     * {@inheritDoc}
     */
    public function setCustomerGroups(Collection $customerGroups): self
    {
        $this->sessionManager->put(
            $this->getSessionKey().'_customer_groups',
            $customerGroups->pluck('handle')->toArray()
        );

        $this->customerGroups = $customerGroups;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setCustomerGroup(CustomerGroupContract $customerGroup): self
    {
        return $this->setCustomerGroups(
            collect([$customerGroup])
        );
    }

    /**
     * Reset the customer groups
     *
     * @return self
     */
    public function resetCustomerGroups()
    {
        $this->sessionManager->forget(
            $this->getSessionKey().'_customer_groups'
        );
        $this->customerGroups = collect();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getChannel(): ChannelContract
    {
        return $this->channel ?: Channel::getDefault();
    }

    /**
     * {@inheritDoc}
     */
    public function getCustomerGroups(): ?Collection
    {
        return $this->customerGroups ?: $this->initCustomerGroups();
    }

    /**
     * {@inheritDoc}
     */
    public function setCurrency(CurrencyContract $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrency(): CurrencyContract
    {
        return $this->currency ?: Currency::getDefault();
    }
}
