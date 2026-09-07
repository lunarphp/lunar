<?php

namespace Lunar\Tests\Checkout\Utils;

use Illuminate\Support\Collection;
use Lunar\Checkout\Contracts\CollectionPointProvider;
use Lunar\Checkout\DataTypes\CollectionPoint;
use Lunar\Core\Models\Cart;

class CollectionPointsStub
{
    /** @param  list<CollectionPoint>  $points */
    public static function bind(array $points): void
    {
        app()->instance(CollectionPointProvider::class, new class($points) implements CollectionPointProvider
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
        app()->forgetInstance(CollectionPointProvider::class);
        app()->offsetUnset(CollectionPointProvider::class);
    }
}
