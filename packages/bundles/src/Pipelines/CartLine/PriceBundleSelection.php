<?php

namespace Lunar\Bundles\Pipelines\CartLine;

use ArrayObject;
use Closure;
use Lunar\Bundles\Contracts\Actions\ResolvesBundleSelection;
use Lunar\Bundles\Enums\BundlePricing;
use Lunar\Bundles\Models\Bundle;
use Lunar\Bundles\Support\ResolvesCartCustomerGroups;
use Lunar\Bundles\ValueObjects\SelectedComponent;
use Lunar\Core\Contracts\PricingManager;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Pricing\PriceCalculatorInterface;

/**
 * Cart-time pricing for a configurable `components` bundle whose selection
 * differs from the default. Runs after `GetUnitPrice` and overwrites the unit
 * price it set; the materialised rows already cover the default selection.
 * Components are priced per bundle unit, so their quantity breaks never apply.
 */
class PriceBundleSelection
{
    use ResolvesCartCustomerGroups;

    public function __construct(
        protected ResolvesBundleSelection $resolver,
        protected PricingManager $pricing,
        protected PriceCalculatorInterface $calculator,
    ) {}

    /**
     * @param  Closure(CartLine): mixed  $next
     */
    public function handle(CartLine $cartLine, Closure $next): mixed
    {
        $purchasable = $cartLine->purchasable;

        if (! $purchasable instanceof ProductVariant) {
            return $next($cartLine);
        }

        /** @var ?Bundle $bundle */
        $bundle = $purchasable->loadMissing('bundle')->bundle;

        if (! $bundle || $bundle->pricing !== BundlePricing::Components) {
            return $next($cartLine);
        }

        $meta = $cartLine->meta instanceof ArrayObject ? $cartLine->meta->getArrayCopy() : (array) $cartLine->meta;
        $selection = $this->resolver->execute($bundle, $meta);

        if ($selection->isDefault()) {
            return $next($cartLine);
        }

        $cart = $cartLine->cart;
        $currency = $cart->currency;
        $customerGroups = $this->customerGroupsFor($cart);
        $sum = 0;

        foreach ($selection as $component) {
            /** @var SelectedComponent $component */
            $matched = $this->pricing
                ->currency($currency)
                ->qty(1)
                ->customerGroups($customerGroups)
                ->for($component->variant)
                ->get()
                ->matched;

            $sum += (int) $matched->price * $component->quantity;
        }

        $discount = $bundle->discount_percentage
            ? $this->calculator->percentage($sum, $bundle->discount_percentage / 100, $currency)
            : 0;

        // A transient row so tax is added exactly as it is for a stored price.
        $price = new Price(['price' => $sum - $discount, 'currency_id' => $currency->getKey()]);
        $price->setRelation('currency', $currency);
        $price->setRelation('priceable', $purchasable);

        $cartLine->unitPrice = new PriceValue($sum - $discount, $currency);
        $cartLine->unitPriceInclTax = new PriceValue($price->priceIncTax($cart->taxZone)->value, $currency);

        return $next($cartLine);
    }
}
