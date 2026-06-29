<?php

namespace Lunar\Core\Managers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Lunar\Core\Contracts\PricingManager as PricingManagerContract;
use Lunar\Core\Contracts\Purchasable;
use Lunar\Core\DataObjects\PricingResponse;
use Lunar\Core\DataObjects\StorefrontContext;
use Lunar\Core\Exceptions\MissingCurrencyPriceException;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;

class PricingManager implements PricingManagerContract
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
    public ?Currency $currency = null;

    /**
     * The quantity value.
     */
    public int $qty = 1;

    /**
     * The customer groups to check against.
     */
    public ?Collection $customerGroups = null;

    /**
     * Whether the user has been resolved (explicitly or from auth).
     */
    protected bool $userResolved = false;

    public function __construct(
        protected AuthFactory $auth,
    ) {}

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
        $this->userResolved = true;

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
        $this->userResolved = true;

        return $this;
    }

    /**
     * Set the currency property.
     *
     * @return self
     */
    public function currency(?Currency $currency)
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
    public function customerGroup(?CustomerGroup $customerGroup)
    {
        $this->customerGroups(
            collect([$customerGroup])
        );

        return $this;
    }

    /**
     * Apply a resolved storefront context. Its currency and customer groups
     * are authoritative, so auth-derived groups do not override them.
     *
     * @return self
     */
    public function using(StorefrontContext $context)
    {
        $this->currency = $context->currency;
        $this->customerGroups = $context->customerGroups;
        $this->userResolved = true;

        return $this;
    }

    /**
     * Get the price for the purchasable.
     *
     * @return PricingResponse
     */
    public function get()
    {
        if (! $this->purchasable) {
            throw new \ErrorException('No purchasable set.');
        }

        $this->resolveUser();

        if (! $this->currency) {
            $this->currency = Currency::getDefault();
        }

        if (! $this->customerGroups || ! $this->customerGroups->count()) {
            $this->customerGroups = collect([
                CustomerGroup::getDefault(),
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
     * Resolve the user from auth on first use, unless one has been set
     * explicitly via user()/guest(). Reads on demand rather than at
     * construction so a manager built before login still sees the user.
     */
    private function resolveUser(): void
    {
        if ($this->userResolved) {
            return;
        }

        $this->userResolved = true;

        $user = $this->auth->guard()->user();

        if ($user && is_lunar_user($user)) {
            $this->user = $user;
        }
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
