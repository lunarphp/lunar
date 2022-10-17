<?php

namespace Lunar\ValueObjects\Cart;

use Illuminate\Pipeline\Pipeline;
use Lunar\Actions\Carts\CalculateLine;
use Lunar\Base\CartLineModifiers;
use Lunar\DataTypes\Price;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;

final class Line
{
    /**
     * The cart line total.
     *
     * @var Price
     */
    public Price $total;

    /**
     * The cart line sub total.
     *
     * @var Price
     */
    public Price $subTotal;

    /**
     * The cart line tax amount.
     *
     * @var Price
     */
    public Price $taxAmount;

    /**
     * The cart line unit price.
     *
     * @var Price
     */
    public Price $unitPrice;

    /**
     * The discount total.
     *
     * @var Price
     */
    public Price $discountTotal;

    /**
     * All the tax breakdowns for the line.
     *
     * @var TaxBreakdown
     */
    public TaxBreakdown $taxBreakdown;

    /**
     * The cart line Eloquent database model.
     *
     * @var CartLine
     */
    public CartLine $model;

    /**
     * Initialize the cart manager.
     *
     * @param  CartLine  $cartLine
     */
    public function __construct(CartLine $model)
    {
        $this->model = $model;
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
        $this->model = $pipeline->send($this->model)->via('calculating')->thenReturn();

        $line = app(CalculateLine::class)->execute(
            $this->model,
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
