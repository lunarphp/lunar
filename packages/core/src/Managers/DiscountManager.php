<?php

namespace Lunar\Managers;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use Lunar\Base\DataTransferObjects\CartDiscount;
use Lunar\Base\DiscountManagerInterface;
use Lunar\Base\Validation\CouponValidator;
use Lunar\DiscountTypes\BuyXGetY;
use Lunar\DiscountTypes\Discount as TypesDiscount;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
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
        TypesDiscount::class,
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
    }

    /**
     * Set a single channel or a collection.
     *
     * @param Channel|iterable $channel
     *
     * @return self
     */
    public function channel(Channel|iterable $channel): self
    {
        $channels = collect(
            !is_iterable($channel) ? [$channel] : $channel
        );

        if ($nonChannel = $channels->filter(fn($channel) => !$channel instanceof Channel)->first()) {
            throw new InvalidArgumentException(
                __('lunar::exceptions.discounts.invalid_channel_type', [
                    'expected' => Channel::class,
                    'actual' => get_class($nonChannel),

                ])
            );
        }
        $this->channels = $channels;

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
            $this->discounts = Discount::active()->orderBy('priority')->get();
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
