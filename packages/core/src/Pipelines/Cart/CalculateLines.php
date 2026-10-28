<?php

namespace Lunar\Core\Pipelines\Cart;

use Closure;
use Illuminate\Pipeline\Pipeline;
use Lunar\Core\Contracts\StepManifest;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Enums\StepList;
use Lunar\Core\Models\Cart;
use Lunar\Core\Modifiers\CartLineModifiers;

class CalculateLines
{
    public function __construct(
        protected CartLineModifiers $cartLineModifiers,
        protected StepManifest $steps,
    ) {}

    /**
     * Called just before cart totals are calculated.
     *
     * @param  Closure(Cart):mixed  $next
     */
    public function handle(Cart $cart, Closure $next): mixed
    {
        /** @var Cart $cart */
        $cart->loadMissing([
            'lines.purchasable.prices.currency',
            'lines.purchasable.prices.priceable',
            'lines.purchasable.product.collections',
            'lines.purchasable.product.brand',
            'lines.purchasable.taxClass',
            'currency',
            'taxZone',
            'shippingAddress',
            'billingAddress',
            'user',
            'customer.customerGroups',
        ]);

        foreach ($cart->lines as $line) {
            $line->setRelation('cart', $cart);

            $line = $this->cartLineModifiers->calculating($line);

            $cartLine = app(Pipeline::class)
                ->send($line)
                ->through($this->steps->for(StepList::CartLinePricing)->all())
                ->thenReturn(function ($cartLine) {
                    $cartLine->cacheProperties();

                    return $cartLine;
                });

            $unitQuantity = $cartLine->purchasable->getUnitQuantity();

            $subTotal = (int) round(($cartLine->unitPrice->value * $cartLine->quantity) / $unitQuantity);

            $cartLine->subTotal = new PriceValue($subTotal, $cart->currency);

            // Derived fields are set after the hook so a modifier's subtotal reaches discounts and tax.
            $cartLine = $this->cartLineModifiers->subtotalled($cartLine);

            $cartLine->taxAmount = new PriceValue(0, $cart->currency);
            $cartLine->total = new PriceValue($cartLine->subTotal->value, $cart->currency);
            $cartLine->subTotalDiscounted = new PriceValue($cartLine->subTotal->value, $cart->currency);
            $cartLine->discountTotal = new PriceValue(0, $cart->currency);
        }

        return $next($cart);
    }
}
