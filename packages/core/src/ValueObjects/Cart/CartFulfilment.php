<?php

namespace Lunar\Core\ValueObjects\Cart;

use Lunar\Core\Contracts\FulfilmentMethod;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Location;

/**
 * How a cart will be fulfilled: the cart default, each line's method (null
 * when it is inferred at order creation) and where pickup lines are collected.
 */
final readonly class CartFulfilment
{
    /**
     * @param  array<int, ?FulfilmentMethod>  $lines  Keyed by cart line id.
     */
    public function __construct(
        public ?FulfilmentMethod $method,
        public array $lines,
        public ?Location $pickupLocation,
    ) {}

    public function forLine(CartLine $line): ?FulfilmentMethod
    {
        return $this->lines[$line->id] ?? null;
    }
}
