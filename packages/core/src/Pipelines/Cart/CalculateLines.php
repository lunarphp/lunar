<?php

namespace Lunar\Core\Pipelines\Cart;

use Closure;
use Illuminate\Pipeline\Pipeline;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Cart;

class CalculateLines
{
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

            $cartLine = app(Pipeline::class)
                ->send($line)
                ->through(
                    config('lunar.cart.pipelines.cart_lines', [])
                )->thenReturn(function ($cartLine) {
                    $cartLine->cacheProperties();

                    return $cartLine;
                });

            $unitQuantity = $cartLine->purchasable->getUnitQuantity();

            $subTotal = (int) round(($cartLine->unitPrice->value * $cartLine->quantity) / $unitQuantity);

            $cartLine->subTotal = new PriceValue($subTotal, $cart->currency);
            $cartLine->taxAmount = new PriceValue(0, $cart->currency);
            $cartLine->total = new PriceValue($subTotal, $cart->currency);
            $cartLine->subTotalDiscounted = new PriceValue($subTotal, $cart->currency);
            $cartLine->discountTotal = new PriceValue(0, $cart->currency);
        }

        return $next($cart);
    }
}
