<?php

namespace Lunar\Core\Actions\Carts;

use Lunar\Core\Contracts\Actions\Carts\SetsShippingOption;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Models\Cart;

class SetShippingOption implements SetsShippingOption
{
    /**
     * Execute the action.
     */
    public function execute(
        Cart $cart,
        ShippingOption $shippingOption
    ): void {
        /** @var Cart $cart */
        $cart->shippingAddress->shippingOption = $shippingOption;
        $cart->shippingAddress->update([
            'shipping_option' => $shippingOption->getIdentifier(),
        ]);
    }
}
