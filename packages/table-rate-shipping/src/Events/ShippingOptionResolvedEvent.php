<?php

namespace Lunar\Shipping\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Lunar\DataTypes\ShippingOption;
use Lunar\Models\Cart;
use Lunar\Shipping\Models\ShippingMethod;

class ShippingOptionResolvedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The resolved shipping option.
     */
    public ShippingOption $shippingOption;

    /**
     * The instance of the shipping method.
     */
    public ShippingMethod $shippingMethod;

    /**
     * The instance of the cart.
     */
    public Cart $cart;

    public function __construct(Cart $cart, ShippingMethod $shippingMethod, ShippingOption $shippingOption)
    {
        $this->cart = $cart;
        $this->shippingMethod = $shippingMethod;
        $this->shippingOption = $shippingOption;
    }
}
