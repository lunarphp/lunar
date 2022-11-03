<?php

namespace Lunar\Managers;

use Illuminate\Support\Collection;
use Lunar\Actions\Carts\CreateOrder;
use Lunar\Actions\Carts\ValidateCartForOrder;
use Lunar\Exceptions\Carts\CartException;
use Lunar\Models\Cart;
use Lunar\Models\CustomerGroup;
use Lunar\Models\TaxZone;

class CartManager
{
    /**
     * The tax zone model.
     *
     * @var TaxZone
     */
    protected TaxZone $taxZone;

    /**
     * The customer groups applied to the cart.
     *
     * @var Collection
     */
    protected Collection $customerGroups;

    /**
     * Initialize the cart manager.
     *
     * @param  Cart  $cart
     */
    public function __construct(
        protected Cart $cart,
    ) {
        $this->customerGroups = $cart->user && $cart->user->customers->count() ?
            $cart->user->customers->map(function ($customer) {
                return $customer->customerGroups;
            })->flatten()
        : collect([CustomerGroup::getDefault()]);

        $this->cart->setManager($this);
    }

    /**
     * Return the cart model instance.
     *
     * @return \Lunar\Models\Cart
     */
    public function getCart(): Cart
    {
        if (is_null($this->cart->total)) {
            $this->calculate();
        }

        return $this->cart;
    }

    /**
     * Returns whether a cart has enough info to create an order.
     *
     * @return bool
     */
    public function canCreateOrder()
    {
        try {
            app(ValidateCartForOrder::class)->execute($this->cart);
        } catch (CartException $e) {
            return false;
        }

        return true;
    }

    public function createOrder()
    {
        $this->calculate();

        return app(CreateOrder::class)->execute($this->cart);
    }

    /**
     * Returns whether the cart has shippable items.
     *
     * @return bool
     */
    public function isShippable()
    {
        return (bool) $this->cart->lines->filter(function ($line) {
            return $line->purchasable->isShippable();
        })->count();
    }
}
