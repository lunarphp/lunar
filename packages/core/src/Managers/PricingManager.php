<?php

namespace Lunar\Core\Managers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Lunar\Core\Contracts\Actions\Storefront\ResolvesStorefrontContext;
use Lunar\Core\Contracts\DefaultStorefrontContext;
use Lunar\Core\Contracts\PricingManager as PricingManagerContract;
use Lunar\Core\Contracts\Purchasable;
use Lunar\Core\Contracts\StepManifest;
use Lunar\Core\DataObjects\PricingResponse;
use Lunar\Core\DataObjects\StorefrontContext;
use Lunar\Core\Enums\StepList;
use Lunar\Core\Exceptions\MissingCurrencyPriceException;
use Lunar\Core\Exceptions\MissingStorefrontContextException;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
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
    public ?Purchasable $purchasable = null;

    /**
     * The quantity value.
     */
    public int $qty = 1;

    /**
     * The user given to the deprecated user() method. Never resolved from auth.
     */
    public private(set) ?Authenticatable $user = null;

    /**
     * Read-only view of the context's currency, kept for existing pipelines.
     */
    public ?Currency $currency {
        get => $this->context?->currency;
    }

    /**
     * Read-only view of the context's customer groups, kept for existing pipelines.
     */
    public ?Collection $customerGroups {
        get => $this->context?->customerGroups;
    }

    /**
     * The single source of pricing state, cleared after every get().
     */
    protected ?StorefrontContext $context = null;

    /**
     * Whether a whole context was supplied through using().
     */
    protected bool $contextSupplied = false;

    protected static bool $preventsDefaultContext = false;

    public function __construct(
        protected DefaultStorefrontContext $defaultContext,
        protected ResolvesStorefrontContext $resolveStorefrontContext,
        protected StepManifest $steps,
    ) {}

    /**
     * Make get() throw when no context was supplied and no default resolver
     * is registered, instead of pricing against store defaults.
     */
    public static function preventDefaultContext(bool $value = true): void
    {
        static::$preventsDefaultContext = $value;
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
     * Apply a resolved storefront context.
     *
     * @return self
     */
    public function using(StorefrontContext $context)
    {
        $this->context = $context;
        $this->contextSupplied = true;

        return $this;
    }

    /**
     * Price for the given customer, deriving its customer groups.
     *
     * @return self
     */
    public function customer(?Customer $customer)
    {
        $context = $this->getContext();

        $this->context = $this->resolveStorefrontContext->execute(
            channel: $context->channel,
            currency: $context->currency,
            language: $context->language,
            customer: $customer,
            region: $context->region,
        );

        return $this;
    }

    /**
     * @deprecated Pass a context with using(), or a customer with customer().
     *
     * @return self
     */
    public function user(?Authenticatable $user)
    {
        if (! $user || ! is_lunar_user($user)) {
            return $this->guest();
        }

        $this->user = $user;

        $customers = $user->loadMissing('customers.customerGroups')->customers;

        // A context names one customer; with several, only their merged groups apply.
        $this->customer($customers->count() === 1 ? $customers->first() : null);

        $customerGroups = $customers->pluck('customerGroups')->flatten()->unique('id')->values();

        if ($customerGroups->isNotEmpty()) {
            $this->context = $this->context->withCustomerGroups($customerGroups);
        }

        return $this;
    }

    /**
     * @deprecated Use customer(null).
     *
     * @return self
     */
    public function guest()
    {
        $this->user = null;

        return $this->customer(null);
    }

    /**
     * Set the currency.
     *
     * @return self
     */
    public function currency(?Currency $currency)
    {
        if ($currency) {
            $this->context = $this->getContext()->withCurrency($currency);
        }

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
     * Set the customer groups. An empty value falls back to the groups of
     * the context's customer, or the default group.
     *
     * @return self
     */
    public function customerGroups(?Collection $customerGroups)
    {
        $customerGroups = $customerGroups?->filter()->values();

        if (! $customerGroups?->count()) {
            return $this->customer($this->getContext()->customer);
        }

        $this->context = $this->getContext()->withCustomerGroups($customerGroups);

        return $this;
    }

    /**
     * Set the customer group.
     *
     * @return self
     */
    public function customerGroup(?CustomerGroup $customerGroup)
    {
        return $this->customerGroups(collect([$customerGroup]));
    }

    public function getContext(): StorefrontContext
    {
        return $this->context ??= $this->defaultContext->get();
    }

    public function getPurchasable(): Purchasable
    {
        return $this->purchasable ?? throw new \ErrorException('No purchasable set.');
    }

    public function getQuantity(): int
    {
        return $this->qty;
    }

    /**
     * Get the price for the purchasable.
     *
     * @return PricingResponse
     */
    public function get()
    {
        try {
            $this->getPurchasable();

            if (static::$preventsDefaultContext && ! $this->contextSupplied && ! $this->defaultContext->hasResolver()) {
                throw new MissingStorefrontContextException;
            }

            $this->getContext();

            $currencyPrices = $this->purchasable->getPrices()->filter(function ($price) {
                return $price->currency_id == $this->context->currency->id;
            });

            if (! $currencyPrices->count()) {
                throw new MissingCurrencyPriceException;
            }

            $prices = $currencyPrices->filter(function ($price) {
                // Only fetch prices which have no customer group (available to all) or belong to the customer groups
                // that we are trying to check against.
                return ! $price->customer_group_id ||
                    $this->context->customerGroups->pluck('id')->contains($price->customer_group_id);
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
                ->through($this->steps->for(StepList::PriceMatching)->all())
                ->then(fn ($pricingManager) => $pricingManager->pricing);

            return $response;
        } finally {
            $this->reset();
        }
    }

    /**
     * Clear all per-call state, so nothing carries into the next get().
     */
    private function reset(): void
    {
        $this->purchasable = null;
        $this->qty = 1;
        $this->user = null;
        $this->context = null;
        $this->contextSupplied = false;
    }
}
