<?php

namespace Lunar\Actions\Carts;

use Lunar\Actions\AbstractAction;
use Lunar\DataTypes\ShippingOption;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;

class SetCheapestShippingOption extends AbstractAction
{
    /**
     * Execute the action.
     *
     * @param  CartLine  $cartLine
     * @param  ShippingOption  $customerGroups
     */
    public function execute(
        Cart $cart
    ): Cart {
        $address = $cart->shippingAddress ?: $cart->dummyShippingAddress;

        $option = ShippingManifest::getOptions($cart)->filter(
            fn ($option) => !$option->collection
        )->sortBy('price.value')->first();

        $address->updateQuietly([
            'shipping_option' => $option?->identifier,
        ]);

        return $cart;
    }
}
