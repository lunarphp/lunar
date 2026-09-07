<?php

namespace Lunar\Tests\Checkout\Utils;

use Illuminate\Support\Collection;
use Lunar\Checkout\Contracts\PickupPointProvider;
use Lunar\Checkout\DataTypes\PickupPoint;
use Lunar\Core\Models\Cart;

class PickupPointsStub
{
    /** @param  list<PickupPoint>  $points */
    public static function bind(array $points): void
    {
        app()->instance(PickupPointProvider::class, new class($points) implements PickupPointProvider
        {
            public function __construct(private array $points) {}

            public function pointsFor(Cart $cart): Collection
            {
                return collect($this->points);
            }
        });
    }

    public static function unbind(): void
    {
        app()->forgetInstance(PickupPointProvider::class);
        app()->offsetUnset(PickupPointProvider::class);
    }
}
