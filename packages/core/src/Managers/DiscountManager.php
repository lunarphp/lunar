<?php

namespace Lunar\Managers;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use Lunar\Base\DataTransferObjects\CartDiscount;
use Lunar\Base\DiscountManagerInterface;
use Lunar\Base\Validation\CouponValidator;
use Lunar\DiscountTypes\AmountOff;
use Lunar\DiscountTypes\BuyXGetY;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Discount;

class DiscountManager implements DiscountManagerInterface
{
    /**
     * The current channels.
     *
     * @var null|Collection<Channel>
     */
    protected ?Collection $channels = null;

    /**
     * The current customer groups
     *
     * @var null|Collection<CustomerGroup>
     */
    protected ?Collection $customerGroups = null;

    /**
     * The available discounts
     *
     * @var null|Collection
     */
    protected ?Collection $discounts = null;

    /**
     * The available discount types
     *
     * @var array
     */
    protected $types = [
        AmountOff::class,
        BuyXGetY::class,
    ];

    /**
     * The applied discounts.
     *
     * @var Collection
     */
    protected Collection $applied;

    /**
     * Instantiate the class.
     */
    public function __construct()
    {
        $this->applied = collect();
        $this->channels = collect();
        $this->customerGroups = collect();
    }

    /**
     * Set a single channel or a collection.
     *
     * @param  Channel|iterable  $channel
     * @return self
     */
    public function channel(Channel|iterable $channel): self
    {
        $channels = collect(
            ! is_iterable($channel) ? [$channel] : $channel
        );

        if ($nonChannel = $channels->filter(fn ($channel) => ! $channel instanceof Channel)->first()) {
            throw new InvalidArgumentException(
                __('lunar::exceptions.discounts.invalid_type', [
                    'expected' => Channel::class,
                    'actual' => $nonChannel->getMorphClass(),
                ])
            );
        }

        $this->channels = $channels;

        return $this;
    }

    /**
     * Set a single customer group or a collection.
     *
     * @param  CustomerGroup|iterable  $customerGroups
     * @return self
     */
    public function customerGroup(CustomerGroup|iterable $customerGroups): self
    {
        $customerGroups = collect(
            ! is_iterable($customerGroups) ? [$customerGroups] : $customerGroups
        );

        if ($nonGroup = $customerGroups->filter(fn ($channel) => ! $channel instanceof CustomerGroup)->first()) {
            throw new InvalidArgumentException(
                __('lunar::exceptions.discounts.invalid_type', [
                    'expected' => CustomerGroup::class,
                    'actual' => $nonGroup->getMorphClass(),
                ])
            );
        }
        $this->customerGroups = $customerGroups;

        return $this;
    }

    /**
     * Return the applied channels.
     *
     * @return Collection
     */
    public function getChannels(): Collection
    {
        return $this->channels;
    }

    /**
     * Returns the available discounts.
     *
     * @return Collection
     */
    public function getDiscounts(): Collection
    {
        if ($this->channels->isEmpty() && $defaultChannel = Channel::getDefault()) {
            $this->channel($defaultChannel);
        }

        if ($this->customerGroups->isEmpty() && $defaultGroup = CustomerGroup::getDefault()) {
            $this->customerGroup($defaultGroup);
        }

        return Discount::active()->whereHas('channels', function ($query) {
            $joinTable = (new Discount)->channels()->getTable();
            $query->whereIn("{$joinTable}.channel_id", $this->channels->pluck('id'))
                ->where("{$joinTable}.enabled", true)
                ->where(function ($query) use ($joinTable) {
                    $query->whereNull("{$joinTable}.starts_at")
                        ->orWhere("{$joinTable}.starts_at", '<=', now());
                })
                ->where(function ($query) use ($joinTable) {
                    $query->whereNull("{$joinTable}.ends_at")
                        ->orWhere("{$joinTable}.ends_at", '>', now());
                });
        })->whereHas('customerGroups', function ($query) {
            $joinTable = (new Discount)->customerGroups()->getTable();

            $query->whereIn("{$joinTable}.customer_group_id", $this->customerGroups->pluck('id'))
                ->where("{$joinTable}.enabled", true)
                ->where(function ($query) use ($joinTable) {
                    $query->whereNull("{$joinTable}.starts_at")
                        ->orWhere("{$joinTable}.starts_at", '<=', now());
                })
                ->where(function ($query) use ($joinTable) {
                    $query->whereNull("{$joinTable}.ends_at")
                        ->orWhere("{$joinTable}.ends_at", '>', now());
                });
        })->orderBy('priority', 'desc')->get();
    }

    /**
     * Return the applied customer groups.
     *
     * @return Collection
     */
    public function getCustomerGroups(): Collection
    {
        return $this->customerGroups;
    }

    public function addType($classname): self
    {
        $this->types[] = $classname;

        return $this;
    }

    public function getTypes(): Collection
    {
        return collect($this->types)->map(function ($class) {
            return app($class);
        });
    }

    public function addApplied(CartDiscount $cartDiscount): self
    {
        $this->applied->push($cartDiscount);

        return $this;
    }

    public function getApplied(): Collection
    {
        return $this->applied;
    }

    public function apply(Cart $cart): Cart
    {
        if (! $this->discounts) {
            $this->discounts = $this->getDiscounts();
        }

        foreach ($this->discounts as $discount) {
            $cart = $discount->getType()->apply($cart);
        }

        return $cart;
    }

    public function validateCoupon(string $coupon): bool
    {
        return app(
            config('lunar.discounts.coupon_validator', CouponValidator::class)
        )->validate($coupon);
    }
}
