<?php

namespace GetCandy\Managers;

use GetCandy\Actions\Carts\CalculateLine;
use GetCandy\Base\CartLineModifiers;
use GetCandy\Models\Cart;
use GetCandy\Models\CartLine;
use Illuminate\Pipeline\Pipeline;

class CartLineManager
{
    /**
     * Initialize the cart manager.
     *
     * @param  \GetCandy\Models\CartLine  $cartLine
     */
    public function __construct(
        protected CartLine $cartLine
    ) {
    }

    /**
     * Calculate the cart totals.
     *
     * @return void
     */
    public function calculate($customerGroups, $shippingAddress = null, $billingAddress = null)
    {
        $pipeline = app(Pipeline::class)
            ->through(
                $this->getModifiers()->toArray()
            );

        $this->cartLine = $pipeline->send($this->cartLine)->via('calculating')->thenReturn();

        $line = app(CalculateLine::class)->execute(
            $this->cartLine,
            $customerGroups,
            $shippingAddress,
            $billingAddress
        );

        return $pipeline->send($line)->via('calculated')->thenReturn();
    }

    /**
     * Return the cart line modifiers.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getModifiers()
    {
        return app(CartLineModifiers::class)->getModifiers();
    }
}
