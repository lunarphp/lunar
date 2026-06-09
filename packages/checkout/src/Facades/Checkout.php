<?php

namespace Lunar\Checkout\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Checkout\Contracts\CheckoutElement;
use Lunar\Checkout\Contracts\ElementRegistry;

/**
 * @method static ElementRegistry add(string|CheckoutElement $element)
 * @method static array<int, CheckoutElement> all()
 * @method static CheckoutElement|null get(string $handle)
 *
 * @see \Lunar\Checkout\ElementRegistry
 */
class Checkout extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ElementRegistry::class;
    }
}
