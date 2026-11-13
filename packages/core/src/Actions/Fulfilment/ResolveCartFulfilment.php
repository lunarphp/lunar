<?php

namespace Lunar\Core\Actions\Fulfilment;

use Lunar\Core\Contracts\Actions\Fulfilment\ResolvesCartFulfilment;
use Lunar\Core\Contracts\FulfilmentMethod;
use Lunar\Core\Contracts\FulfilmentMethodManifest;
use Lunar\Core\Drivers\FulfilmentMethods\Pickup;
use Lunar\Core\Drivers\FulfilmentMethods\Shipping;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Location;
use Lunar\Core\ValueObjects\Cart\CartFulfilment;

/**
 * The cart default is a preference, never a coercion: a line the default
 * can't fulfil keeps its own method, or none, and is claimed at order creation.
 */
class ResolveCartFulfilment implements ResolvesCartFulfilment
{
    public function __construct(
        protected FulfilmentMethodManifest $fulfilmentMethods,
    ) {}

    public function execute(Cart $cart, bool $withShippingOption = true): CartFulfilment
    {
        /** @var Cart $cart */
        $shippingOption = $withShippingOption ? $cart->getShippingOption() : null;

        $default = $this->fulfilmentMethods->get($cart->fulfilment_method)
            ?? match (true) {
                $shippingOption === null => null,
                $shippingOption->isPickup() => $this->fulfilmentMethods->get(Pickup::KEY),
                default => $this->fulfilmentMethods->get(Shipping::KEY),
            };

        $lines = $cart->lines->mapWithKeys(
            fn (CartLine $line) => [$line->id => $this->resolveLine($line, $default)]
        )->all();

        $hasPickup = collect($lines)->contains(fn (?FulfilmentMethod $method) => $method?->getKey() === Pickup::KEY);

        $pickupLocation = $hasPickup
            ? ($cart->pickupLocation ?? $shippingOption?->pickupLocation ?? Location::getDefault())
            : null;

        return new CartFulfilment($default, $lines, $pickupLocation);
    }

    protected function resolveLine(CartLine $line, ?FulfilmentMethod $default): ?FulfilmentMethod
    {
        if ($explicit = $this->fulfilmentMethods->get($line->fulfilment_method)) {
            return $explicit;
        }

        if (! $purchasable = $line->purchasable) {
            return null;
        }

        $supporting = $this->fulfilmentMethods->supporting($purchasable);

        if ($default && $supporting->has($default->getKey())) {
            return $default;
        }

        // Keeps a restricted item on a method it allows. An unrestricted item
        // stays null, since the first supporting method would be pickup.
        return $purchasable->allowedFulfilmentMethods() !== null ? $supporting->first() : null;
    }
}
