<?php

namespace Lunar\Checkout\Support;

use Illuminate\Support\Collection;
use Lunar\Checkout\Contracts\LocatesCustomer;
use Lunar\Checkout\Contracts\PickupPointProvider;
use Lunar\Checkout\DataTypes\Coordinates;
use Lunar\Checkout\DataTypes\PickupPoint;
use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Models\Cart;

/**
 * Reads the collect state a cart carries (spec 0013 §B). The only place the
 * two `cart.meta` keys are spelled; `Actions\SetFulfilment` is the only writer.
 */
final class PickupPoints
{
    public const MODE_KEY = 'fulfilment';

    public const POINT_KEY = 'pickup_point';

    public const DELIVERY = 'delivery';

    public const COLLECT = 'collect';

    /**
     * @return Collection<int, PickupPoint>
     */
    public static function offered(Cart $cart): Collection
    {
        if (! app()->bound(PickupPointProvider::class)) {
            return collect();
        }

        return app(PickupPointProvider::class)->pointsFor($cart)->values();
    }

    /**
     * Where the customer is, if the bound provider can say (spec 0013 §A).
     * Null when no provider is bound, the provider does not locate, or it
     * cannot place this cart.
     */
    public static function origin(Cart $cart): ?Coordinates
    {
        if (! app()->bound(PickupPointProvider::class)) {
            return null;
        }

        $provider = app(PickupPointProvider::class);

        return $provider instanceof LocatesCustomer ? $provider->originFor($cart) : null;
    }

    public static function find(Cart $cart, string $handle): ?PickupPoint
    {
        return self::offered($cart)->first(fn (PickupPoint $point): bool => $point->handle === $handle);
    }

    /**
     * The stored snapshot, or null when none is stored or the stored one is
     * no longer offered for this cart (spec 0013 §B: treated as unselected).
     *
     * @return array{handle: string, name: string, lines: list<string>, meta: array<string, mixed>, location?: array{latitude: float, longitude: float}|null}|null
     */
    public static function chosen(Cart $cart): ?array
    {
        $stored = $cart->meta[self::POINT_KEY] ?? null;

        if (! is_array($stored) || ! isset($stored['handle'])) {
            return null;
        }

        $offered = self::offered($cart);

        if ($offered->isNotEmpty() && self::find($cart, (string) $stored['handle']) === null) {
            return null;
        }

        return PickupPoint::fromArray($stored)->toArray();
    }

    public static function chosenHandle(Cart $cart): ?string
    {
        return self::chosen($cart)['handle'] ?? null;
    }

    /**
     * The customer's mode. Falls back to the stored option for carts that
     * predate the meta key.
     */
    public static function fulfilment(Cart $cart): string
    {
        $mode = $cart->meta[self::MODE_KEY] ?? null;

        if ($mode === self::COLLECT || $mode === self::DELIVERY) {
            return $mode;
        }

        return self::storedOptionCollects($cart) ? self::COLLECT : self::DELIVERY;
    }

    /**
     * True when the cart collects, the provider offers points, and no offered
     * point is chosen. The validator (§D) and the pay boundary share this.
     */
    public static function missing(Cart $cart): bool
    {
        if (! self::storedOptionCollects($cart)) {
            return false;
        }

        if (self::offered($cart)->isEmpty()) {
            return false;
        }

        return self::chosen($cart) === null;
    }

    public static function storedOptionCollects(Cart $cart): bool
    {
        $identifier = $cart->shippingAddress?->shipping_option;

        if ($identifier === null) {
            return false;
        }

        return (bool) ShippingManifest::getOption($cart, $identifier)?->collect;
    }
}
