<?php

namespace GetCandy\Managers;

use GetCandy\Actions\Carts\CreateOrder;
use GetCandy\Actions\Carts\MergeCart;
use GetCandy\Actions\Carts\ValidateCartForOrder;
use GetCandy\Base\Addressable;
use GetCandy\Base\CartModifiers;
use GetCandy\Base\Purchasable;
use GetCandy\DataTypes\Price;
use GetCandy\DataTypes\ShippingOption;
use GetCandy\Exceptions\CartLineIdMismatchException;
use GetCandy\Exceptions\Carts\CartException;
use GetCandy\Exceptions\Carts\ShippingAddressMissingException;
use GetCandy\Exceptions\InvalidCartLineQuantityException;
use GetCandy\Exceptions\MaximumCartLineQuantityException;
use GetCandy\Facades\ShippingManifest;
use GetCandy\Models\Cart;
use GetCandy\Models\CartAddress;
use GetCandy\Models\CartLine;
use GetCandy\Models\CustomerGroup;
use GetCandy\Models\TaxZone;
use Illuminate\Foundation\Auth\User;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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

    // protected ShippingZone $shippingZone = null

    /**
     * Initialize the cart manager.
     *
     * @param Cart $cart
     */
    public function __construct(
        protected Cart $cart,
    ) {
        $this->customerGroups = $cart->user && $cart->user->customers->count() ?
            $cart->user->customers->map(function ($customer) {
                return $customer->customerGroups;
            })->flatten()
        : collect([CustomerGroup::getDefault()]);
    }

    /**
     * Calculate the cart totals.
     *
     * @return self
     */
    public function calculate()
    {
        $pipeline = app(Pipeline::class)
            ->send($this->cart)
            ->through(
                $this->getModifiers()->toArray()
            );

        $pipeline->via('calculating')->thenReturn();

        $lines = $this->calculateLines();

        // Get the line subtotals and add together.
        $subTotal = $lines->sum('subTotal.value');
        $discountTotal = $lines->sum('discountTotal.value');
        $taxTotal = $lines->sum('taxAmount.value');
        $total = $lines->sum('total.value');
        $taxBreakDown = $lines->pluck('taxBreakdown')->flatten();

        $this->cart->subTotal = new Price($subTotal, $this->cart->currency, 1);
        $this->cart->discountTotal = new Price($discountTotal, $this->cart->currency, 1);

        if ($shippingOption = $this->getShippingOption()) {
            $shippingTax = app(TaxManager::class)
                            ->setShippingAddress($this->cart->shippingAddress)
                            ->setCurrency($this->cart->currency)
                            ->setPurchasable($shippingOption)
                            ->getBreakdown($shippingOption->price->value);

            $shippingSubTotal = $shippingOption->price->value;
            $shippingTaxTotal = $shippingTax->sum('total.value');
            $shippingTotal = $shippingSubTotal + $shippingTaxTotal;

            $taxBreakDown = $taxBreakDown->merge($shippingTax);

            $taxTotal += $shippingTaxTotal;
            $total += $shippingTotal;

            $this->cart->shippingAddress->taxBreakdown = $taxBreakDown;
            $this->cart->shippingAddress->shippingTotal = new Price($shippingTotal, $this->cart->currency, 1);
            $this->cart->shippingAddress->shippingTaxTotal = new Price($shippingTaxTotal, $this->cart->currency, 1);
            $this->cart->shippingAddress->shippingSubTotal = new Price($shippingOption->price->value, $this->cart->currency, 1);

            $this->cart->shippingTotal = new Price($shippingOption->price->value, $this->cart->currency, 1);
        }

        $this->cart->taxTotal = new Price($taxTotal, $this->cart->currency, 1);
        $this->cart->total = new Price($total, $this->cart->currency, 1);

        // Need to include shipping tax breakdown...
        $this->cart->taxBreakdown = $taxBreakDown->groupBy('tax_rate_id')->map(function ($amounts) {
            return [
                'rate'    => $amounts->first()->taxRate,
                'amounts' => $amounts,
                'total'   => new Price($amounts->sum('total.value'), $this->cart->currency, 1),
            ];
        });

        $pipeline->via('calculated')->thenReturn();

        return $this;
    }

    /**
     * Return the cart model instance.
     *
     * @return \GetCandy\Models\Cart
     */
    public function getCart(): Cart
    {
        if (is_null($this->cart->total)) {
            $this->calculate();
        }

        return $this->cart;
    }

    /**
     * Add a line to the cart.
     *
     * @param Purchasable $purchasable
     * @param int         $quantity
     * @param array       $meta
     *
     * @return void
     */
    public function add(Purchasable $purchasable, int $quantity = 1, $meta = [])
    {
        if ($quantity < 1) {
            throw new InvalidCartLineQuantityException(
                __('getcandy::exceptions.invalid_cart_line_quantity', [
                    'quantity' => $quantity,
                ])
            );
        }

        if ($quantity > 1000000) {
            throw new MaximumCartLineQuantityException();
        }

        // Do we already have this line?
        $existing = $this->cart->lines->first(function ($line) use ($purchasable, $meta) {
            return $line->purchasable_id == $purchasable->id &&
            $line->purchasable_type == get_class($purchasable) &&
            json_encode($line->meta) == json_encode($meta);
        });

        if ($existing) {
            $existing->update([
                'quantity' => $existing->quantity + $quantity,
            ]);

            return true;
        }

        $this->cart->lines()->create([
            'purchasable_id'   => $purchasable->id,
            'purchasable_type' => get_class($purchasable),
            'quantity'         => $quantity,
            'meta'             => $meta,
        ]);

        return true;
    }

    /**
     * Remove a cart line from the cart.
     *
     * @param int|string $cartLineId
     *
     * @throws \GetCandy\Exceptions\CartLineIdMismatchException
     *
     * @return \GetCandy\Models\Cart
     */
    public function removeLine($cartLineId)
    {
        // If we're trying to remove a line that does not
        // belong to this cart, throw an exception.
        $line = $this->cart->lines()->whereId($cartLineId)->first();

        if (!$line) {
            throw new CartLineIdMismatchException(
                __('getcandy::exceptions.cart_line_id_mismatch')
            );
        }

        $line->delete();

        return $this->calculate()->getCart();
    }

    /**
     * Update cart lines.
     *
     * @param Collection $lines
     *
     * @return \GetCandy\Models\Cart
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
     * @param string|int $id
     * @param int        $quantity
     * @param array|null $meta
     *
     * @return void
     */
    public function updateLine($id, int $quantity, $meta = null)
    {
        if ($quantity < 1) {
            throw new InvalidCartLineQuantityException(
                __('getcandy::exceptions.invalid_cart_line_quantity', [
                    'quantity' => $quantity,
                ])
            );
        }

        if ($quantity > 1000000) {
            throw new MaximumCartLineQuantityException();
        }

        CartLine::whereId($id)->update([
            'quantity' => $quantity,
            'meta'     => $meta,
        ]);
    }

    /**
     * Associate a user to the cart.
     *
     * @param User   $user
     * @param string $policy
     *
     * @return \GetCandy\Models\Cart
     */
    public function associate(User $user, $policy = 'merge')
    {
        if ($policy == 'merge') {
            $userCart = Cart::whereUserId($user->id)->unMerged()->latest()->first();
            if ($userCart) {
                $this->cart = app(MergeCart::class)->execute($userCart, $this->cart);
            }
        }

        if ($policy == 'override') {
            $userCart = Cart::whereUserId($user->id)->unMerged()->latest()->first();
            if ($userCart && $userCart->id != $this->cart->id) {
                $userCart->update([
                    'merged_id' => $userCart->id,
                ]);
            }
        }

        $this->cart->update([
            'user_id' => $user->id,
        ]);

        return $this->cart;
    }

    /**
     * Set the shipping address.
     *
     * @param \GetCandy\Base\Addressable|array $address
     *
     * @return self
     */
    public function setShippingAddress(array|Addressable $address)
    {
        $this->cart->shippingAddress?->delete();

        $this->addAddress($address, 'shipping');

        $this->cart->load('shippingAddress');

        return $this->calculate()->getCart();
    }

    /**
     * Set the billing address.
     *
     * @param array|Addressable $address
     *
     * @return self
     */
    public function setBillingAddress(array|Addressable $address)
    {
        $this->cart->billingAddress?->delete();

        $this->addAddress($address, 'billing');

        $this->cart->load('billingAddress');

        return $this;
    }

    /**
     * Set the shipping option to the shipping address.
     *
     * @param ShippingOption $option
     *
     * @throws \GetCandy\Exceptions\Carts\ShippingAddressMissingException
     *
     * @return self
     */
    public function setShippingOption(ShippingOption $option)
    {
        if (!$this->cart->shippingAddress) {
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
        if (!$this->cart->shippingAddress) {
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
     * @param array|Addressable $address
     * @param [type] $type
     *
     * @return void
     */
    private function addAddress(array|Addressable $address, $type)
    {
        if ($address instanceof Addressable) {
            $address = $address->only(
                (new CartAddress())->getFillable()
            );
        }
        $address['type'] = $type;
        $this->cart->addresses()->create($address);
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
