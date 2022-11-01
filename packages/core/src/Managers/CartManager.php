<?php

namespace Lunar\Managers;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Lunar\Actions\Carts\CreateOrder;
use Lunar\Actions\Carts\MergeCart;
use Lunar\Actions\Carts\ValidateCartForOrder;
use Lunar\Base\Addressable;
use Lunar\Base\CartModifiers;
use Lunar\DataTypes\ShippingOption;
use Lunar\Exceptions\CartLineIdMismatchException;
use Lunar\Exceptions\Carts\CartException;
use Lunar\Exceptions\Carts\ShippingAddressMissingException;
use Lunar\Exceptions\InvalidCartLineQuantityException;
use Lunar\Exceptions\MaximumCartLineQuantityException;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\CartLine;
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
     * Add cart lines.
     *
     * @param  iterable  $lines
     * @return bool
     */
    public function addLines(iterable $lines)
    {
        collect($lines)->each(function ($line) {
            $this->add(
                $line['purchasable'],
                $line['quantity'],
                $line['meta'] ?? null
            );
        });

        return true;
    }

    /**
     * Remove a cart line from the cart.
     *
     * @param  int|string  $cartLineId
     * @return \Lunar\Models\Cart
     *
     * @throws \Lunar\Exceptions\CartLineIdMismatchException
     */
    public function removeLine($cartLineId)
    {
        // If we're trying to remove a line that does not
        // belong to this cart, throw an exception.
        $line = $this->cart->lines()->whereId($cartLineId)->first();

        if (! $line) {
            throw new CartLineIdMismatchException(
                __('lunar::exceptions.cart_line_id_mismatch')
            );
        }

        $line->delete();

        return $this->calculate()->getCart();
    }

    /**
     * Deletes all cart lines.
     */
    public function clear()
    {
        $this->cart->lines()->delete();

        return $this->calculate()->getCart();
    }

    /**
     * Update cart lines.
     *
     * @param  Collection  $lines
     * @return \Lunar\Models\Cart
     */
    public function updateLines(Collection $lines)
    {
        DB::transaction(function () use ($lines) {
            $lines->each(function ($line) {
                $this->updateLine(
                    $line['id'],
                    $line['quantity'],
                    $line['meta'] ?? null
                );
            });
        });

        return $this->calculate()->getCart();
    }

    /**
     * Update a cart line.
     *
     * @param  string|int  $id
     * @param  int  $quantity
     * @param  array|null  $meta
     * @return void
     */
    public function updateLine($id, int $quantity, $meta = null)
    {
        if ($quantity < 1) {
            throw new InvalidCartLineQuantityException(
                __('lunar::exceptions.invalid_cart_line_quantity', [
                    'quantity' => $quantity,
                ])
            );
        }

        if ($quantity > 1000000) {
            throw new MaximumCartLineQuantityException();
        }

        CartLine::whereId($id)->update([
            'quantity' => $quantity,
            'meta' => (array) $meta,
        ]);
    }

    /**
     * Associate a user to the cart.
     *
     * @param  User  $user
     * @param  string  $policy
     * @return \Lunar\Models\Cart
     */
    public function associate(User $user, $policy = 'merge')
    {
        if ($policy == 'merge') {
            $userCart = Cart::whereUserId($user->getKey())->unMerged()->latest()->first();
            if ($userCart) {
                $this->cart = app(MergeCart::class)->execute($userCart, $this->cart);
            }
        }

        if ($policy == 'override') {
            $userCart = Cart::whereUserId($user->getKey())->unMerged()->latest()->first();
            if ($userCart && $userCart->id != $this->cart->id) {
                $userCart->update([
                    'merged_id' => $userCart->id,
                ]);
            }
        }

        $this->cart->update([
            'user_id' => $user->getKey(),
        ]);

        return $this->cart;
    }

    /**
     * Set the shipping address.
     *
     * @param  \Lunar\Base\Addressable|array  $address
     * @return \Lunar\Models\Cart
     */
    public function setShippingAddress(array|Addressable $address)
    {
        $this->addAddress($address, 'shipping');

        $this->cart->load('shippingAddress');

        return $this->calculate()->getCart();
    }

    /**
     * Set the billing address.
     *
     * @param  array|Addressable  $address
     * @return self
     */
    public function setBillingAddress(array|Addressable $address)
    {
        $this->addAddress($address, 'billing');

        $this->cart->load('billingAddress');

        return $this;
    }

    /**
     * Set the shipping option to the shipping address.
     *
     * @param  ShippingOption  $option
     * @return self
     *
     * @throws \Lunar\Exceptions\Carts\ShippingAddressMissingException
     */
    public function setShippingOption(ShippingOption $option)
    {
        if (! $this->cart->shippingAddress) {
            throw new ShippingAddressMissingException();
        }
        $this->cart->shippingAddress->shippingOption = $option;

        $this->cart->shippingAddress->update([
            'shipping_option' => $option->getIdentifier(),
        ]);

        $this->calculate();

        return $this;
    }

    public function getShippingOption()
    {
        if (! $this->cart->shippingAddress) {
            return null;
        }

        return ShippingManifest::getOptions($this->cart)->first(function ($option) {
            return $option->getIdentifier() == $this->cart->shippingAddress->shipping_option;
        });
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

    /**
     * Add an address to the.
     *
     * @param  array|Addressable  $address
     * @param [type] $type
     * @return void
     */
    private function addAddress(array|Addressable $address, $type)
    {
        // Do we already have an address for this type?
        $existing = $this->cart->addresses()->whereType($type)->first();

        if (is_array($address)) {
            $address = new CartAddress($address);
        }

        if ($existing) {
            $address = $existing->fill(
                $address->getAttributes()
            );
        }

        // If we have an id but the types don't match. We need to treat
        // it as a new address being added using an existing as the base.
        if ($address->type != $type && $address->id) {
            $address->id = null;
        }

        // Force the type.
        $address->type = $type;

        if ($address->id) {
            $this->cart->addresses()->save($address);
        } else {
            $this->cart->addresses()->create(
                $address->toArray()
            );
        }
    }

    /**
     * Return the cart modifiers.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getModifiers()
    {
        return app(CartModifiers::class)->getModifiers();
    }

    /**
     * Calculate the cart lines.
     *
     * @return \Illuminate\Support\Collection
     */
    private function calculateLines()
    {
        return $this->cart->lines->map(function ($line) {
            return (new CartLineManager($line))->calculate(
                $this->customerGroups,
                $this->cart->shippingAddress,
                $this->cart->billingAddress
            );
        });
    }
}
