<?php

namespace Lunar\Managers;

use Illuminate\Session\SessionManager;
use Illuminate\Support\Collection;
use Lunar\Base\StorefrontSessionInterface;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;

class StorefrontSessionManager implements StorefrontSessionInterface
{
    /**
     * The current channel
     *
     * @var Channel|null
     */
    protected ?Channel $channel = null;

    /**
     * The collection of customer groups to use.
     *
     * @var Collection
     */
    protected ?Collection $customerGroups = null;

    /**
     * Initialise the manager
     *
     * @param protected SessionManager
     */
    public function __construct(
        protected SessionManager $sessionManager
    ) {
        if (!$this->customerGroups) {
            $this->customerGroups = collect();
        }

        $this->initChannel();
        $this->initCustomerGroups();
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
            if (!$groupHandles) {
                return $this->setCustomerGroups(
                    $this->customerGroups
                );
            }
            return $this->customerGroups;
        }


//         if ($this->customerGroup && $this->customerGroup->handle == $handle) {
//             return $this->customerGroup;
//         }
//
//         if (!$handle) {
//             return $this->setCustomerGroup(
//                 CustomerGroup::getDefault()
//             );
//         }
//
//         $model = CustomerGroup::whereHandle($handle)->first();
//
//         if (!$model) {
//             throw new \Exception(
//                 "Unable to find customer group with handle {$handle}"
//             );
//         }

        // return $this->setCustomerGroup($model);
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

        if (!$channelHandle) {
            return $this->setChannel(
                Channel::getDefault()
            );
        }

        $channel = Channel::whereHandle($channelHandle)->first();

        if (!$channel) {
            throw new \Exception(
                "Unable to find channel with handle {$channelHandle}"
            );
        }

        return $this->setChannel($channel);
    }

    /**
     * {@inheritDoc}
     */
    public function getSessionKey()
    {
        return 'lunar_storefront';
    }

    /**
     * {@inheritDoc}
     */
    public function setChannel(Channel|string $channel)
    {
        $this->sessionManager->put(
            $this->getSessionKey().'_channel',
            $channel->handle
        );
        $this->channel = $channel;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setCustomerGroups(Collection $customerGroups)
    {
        $this->sessionManager->put(
            $this->getSessionKey().'_customer_group',
            $customerGroups->pluck('handle')
        );

        $this->customerGroups = $customerGroups;
        return $this;
    }

    /**
     * Reset the customer groups
     *
     * @return self
     */
    public function resetCustomerGroups()
    {
        $this->sessionManager->forget(
            $this->getSessionKey().'_customer_group'
        );
        $this->customerGroups = collect();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getChannel(): Channel
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
    public function setCurrency(Currency $currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrency(): Currency
    {
        return $this->currency ?: Currency::getDefault();
    }
}
