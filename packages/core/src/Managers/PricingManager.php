<?php

namespace Lunar\Managers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Lunar\Base\DataTransferObjects\PricingResponse;
use Lunar\Base\PricingManagerInterface;
use Lunar\Base\Purchasable;
use Lunar\Exceptions\MissingCurrencyPriceException;
use Lunar\Models\Contracts\Currency as CurrencyContract;
use Lunar\Models\Contracts\CustomerGroup as CustomerGroupContract;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;

class PricingManager implements PricingManagerInterface
{
    /**
     * The DTO of the pricing.
     */
    public PricingResponse $pricing;

    /**
     * The instance of the purchasable model.
     */
    public Purchasable $purchasable;

    /**
     * The instance of the user.
     */
    public ?Authenticatable $user = null;

    /**
     * The instance of the currency.
     */
    public ?CurrencyContract $currency = null;

    /**
     * The quantity value.
     */
    public int $qty = 1;

    /**
     * The customer groups to check against.
     */
    public ?Collection $customerGroups = null;

    public function __construct()
    {
        if (Auth::check() && is_lunar_user(Auth::user())) {
            $this->user = Auth::user();
        }
    }

    /**
     * Set the purchasable property.
     *
     * @return self
     */
    public function for(Purchasable $purchasable)
    {
        $this->purchasable = $purchasable;

        return $this;
    }

    /**
     * Set the user property.
     *
     * @return self
     */
    public function user(?Authenticatable $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Set the user property to NULL.
     *
     * @return self
     */
    public function guest()
    {
        $this->user = null;

        return $this;
    }

    /**
     * Set the currency property.
     *
     * @return self
     */
    public function currency(?CurrencyContract $currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Set the quantity property.
     *
     * @return self
     */
    public function qty(int $qty)
    {
        $this->qty = $qty;

        return $this;
    }

    /**
     * Set the customer groups.
     *
     * @return self
     */
    public function customerGroups(?Collection $customerGroups)
    {
        $this->customerGroups = $customerGroups;

        return $this;
    }

    /**
     * Set the customer group.
     *
     * @return self
     */
    public function customerGroup(?CustomerGroupContract $customerGroup)
    {
        $this->customerGroups(
            collect([$customerGroup])
        );

        return $this;
    }

    /**
     * Get the price for the purchasable.
     *
     * @return \Lunar\Base\DataTransferObjects\PricingResponse
     */
    public function get()
    {
        if (! $this->purchasable) {
            throw new \ErrorException('No purchasable set.');
        }

        if (! $this->currency) {
            $this->currency = Currency::modelCLass()::getDefault();
        }

        if (! $this->customerGroups || ! $this->customerGroups->count()) {
            $this->customerGroups = collect([
                CustomerGroup::modelClass()::getDefault(),
            ]);
        }

        // Do we have a user?
        if ($this->user && $this->user->customers->count()) {
            $customers = $this->user->customers;
            $customerGroups = $customers->pluck('customerGroups')->flatten();

            if ($customerGroups->count()) {
                $this->customerGroups = $customerGroups;
            }
        }

        $currencyPrices = $this->purchasable->getPrices()->filter(function ($price) {
            return $price->currency_id == $this->currency->id;
        });

        if (! $currencyPrices->count()) {
            throw new MissingCurrencyPriceException;
        }

        $prices = $currencyPrices->filter(function ($price) {
            // Only fetch prices which have no customer group (available to all) or belong to the customer groups
            // that we are trying to check against.
            return ! $price->customer_group_id ||
                $this->customerGroups->pluck('id')->contains($price->customer_group_id);
        })->sortBy('price');

        // Get our base price
        $basePrice = $prices->first(fn ($price) => $price->min_quantity == 1 && ! $price->customer_group_id);

        // To start, we'll set the matched price to the base price.
        $matched = $basePrice;

        // If we have customer group prices, we should find the cheapest one and send that back.
        $potentialGroupPrice = $prices->filter(function ($price) {
            return (bool) $price->customer_group_id && ($price->min_quantity == 1);
        })->sortBy('price');

        $matched = $potentialGroupPrice->first() ?: $matched;

        // Get all price breaks that match for the given quantity. These take priority over the other steps
        // as we could be bulk purchasing.
        $priceBreaks = $prices->filter(function ($price) {
            return $price->min_quantity > 1 && $this->qty >= $price->min_quantity;
        })->sortBy('price');

        $matched = $priceBreaks->first() ?: $matched;

        if (! $matched) {
            throw new \ErrorException('No price set.');
        }

        $this->pricing = new PricingResponse(
            matched: $matched,
            base: $prices->first(fn ($price) => $price->min_quantity == 1),
            priceBreaks: $prices->filter(fn ($price) => $price->min_quantity > 1),
            customerGroupPrices: $prices->filter(fn ($price) => (bool) $price->customer_group_id)
        );

        $response = app(Pipeline::class)
            ->send($this)
            ->through(
                config('lunar.pricing.pipelines', [])
            )->then(fn ($pricingManager) => $pricingManager->pricing);

        $this->reset();

        return $response;
    }

    /**
     * Reset the manager into a base instance.
     *
     * @return void
     */
    private function reset()
    {
        $this->qty = 1;
        $this->customerGroups = null;
    }
}
