<?php

namespace GetCandy\Managers;

use GetCandy\Base\CartLineModifier;
use GetCandy\Base\CartLineModifiers;
use GetCandy\Base\CartModifier;
use GetCandy\Base\DataTransferObjects\CartDiscount;
use GetCandy\Base\DiscountManagerInterface;
use GetCandy\DiscountTypes\Coupon;
use GetCandy\DiscountTypes\ProductDiscount;
use GetCandy\Models\CartLine;
use GetCandy\Models\Discount;
use Illuminate\Support\Collection;

class DiscountManager implements DiscountManagerInterface
{
    protected $discounts = null;

    protected $types = [
        Coupon::class,
        ProductDiscount::class,
    ];

    /**
     * The applied discounts.
     *
     * @var Collection
     */
    protected Collection $applied;

    public function __construct()
    {
        $this->applied = collect();
    }

    public function addType($classname)
    {
        $this->types[] = $classname;

        return $this;
    }

    public function getTypes()
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

    public function getApplied()
    {
        return $this->applied;
    }

    public function apply(CartLine $cartLine)
    {
        if (! $this->discounts) {
            $this->discounts = Discount::active()->orderBy('priority')->get();
        }

        foreach ($this->discounts as $discount) {
            $cartLine = $discount->type()->execute($cartLine);
        }

        return $cartLine;
    }
}
