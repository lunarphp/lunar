<?php

namespace GetCandy\Managers;

use GetCandy\Base\DataTransferObjects\PricingResponse;
use GetCandy\Base\PricingManagerInterface;
use GetCandy\Base\Purchasable;
use GetCandy\Models\Currency;
use Illuminate\Auth\Authenticatable;

class PricingManager implements PricingManagerInterface
{
    /**
     * The instance of the user
     *
     * @var \Illuminate\Auth\Authenticatable
     */
    protected ?Authenticatable $user = null;

    /**
     * The instance of the currency.
     *
     * @var \GetCandy\Models\Currency
     */
    protected ?Currency $currency = null;

    /**
     * The quantity value.
     *
     * @var integer
     */
    protected int $qty = 1;

    /**
     * The instance of the purchasable object.
     *
     * @var \GetCandy\Base\Purchasable
     */
    protected Purchasable $purchasable;

    /**
     * Set the user property.
     *
     * @param \Illuminate\Auth\Authenticatable $user
     * @return self
     */
    public function user(Authenticatable $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Set the currency property.
     *
     * @param \GetCandy\Models\Currency $currency
     * @return self
     */
    public function currency(Currency $currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * Set the quantity property.
     *
     * @param integer $qty
     * @return self
     */
    public function qty(int $qty)
    {
        $this->qty = $qty;
        return $this;
    }

    /**
     * Get the price for a purchasable.
     *
     * @param Purchasable $purchasable
     * @return \GetCandy\Base\DataTransferObjects\PricingResponse
     */
    public function for(Purchasable $purchasable)
    {
        if (!$this->currency) {
            $this->currency = Currency::getDefault();
        }

        $prices = $purchasable->getPrices()->filter(function ($price) {
            return $price->currency_id == $this->currency->id;
        })->sortBy('price');

        $price = $prices->first(fn($price) => $price->tier <= $this->qty);

        $basePrice = $prices->first(fn($price) => $price->tier == 1);

        $tieredPrices = $prices->filter(fn($price) => $price->tier > 1);

        return new PricingResponse(
            matched: $price,
            base: $basePrice,
            tiered: $tieredPrices,
            customerGroupPrices: collect()
        );
    }
}
