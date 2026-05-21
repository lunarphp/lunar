<?php

namespace Lunar\Core\Actions\Carts;

use Lunar\Core\Actions\AbstractAction;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Contracts\Cart as CartContract;

class SetShippingOption extends AbstractAction
{
    /**
     * Execute the action.
     */
    public function execute(
        CartContract $cart,
        ShippingOption $shippingOption
    ): self {
        /** @var Cart $cart */
        $cart->shippingAddress->shippingOption = $shippingOption;
        $cart->shippingAddress->update([
            'shipping_option' => $shippingOption->getIdentifier(),
        ]);

        return $this;
    }
}
