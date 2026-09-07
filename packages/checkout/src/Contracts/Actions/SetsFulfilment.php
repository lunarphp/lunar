<?php

namespace Lunar\Checkout\Contracts\Actions;

use Illuminate\Validation\ValidationException;
use Lunar\Core\Models\Cart;

/**
 * The only writer of a cart's fulfilment mode and collection point
 * (spec 0013 §B). Takes a cart, not a session, so a host's basket page can
 * call it before any checkout session exists (§G).
 */
interface SetsFulfilment
{
    /**
     * @param  'delivery'|'collect'  $mode
     *
     * @throws ValidationException on an unknown mode
     *                             (`fulfilment`) or a point the provider does not offer
     *                             (`collection_point`)
     */
    public function execute(Cart $cart, string $mode, ?string $collectionPoint = null): Cart;
}
