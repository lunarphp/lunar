<?php

namespace Lunar\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Core\Contracts\FulfilmentManager;

/**
 * @method static \Lunar\Core\Models\Fulfilment create(\Lunar\Core\Models\Contracts\Order $order, array $lines, array $attributes = [])
 * @method static \Lunar\Core\Models\Fulfilment split(\Lunar\Core\Models\Contracts\Fulfilment $fulfilment, array $moves)
 * @method static \Lunar\Core\Models\Fulfilment merge(\Lunar\Core\Models\Contracts\Fulfilment $target, \Illuminate\Database\Eloquent\Collection $sources)
 * @method static \Lunar\Core\Models\Fulfilment move(\Lunar\Core\Models\Contracts\Fulfilment $from, \Lunar\Core\Models\Contracts\Fulfilment $to, array $moves)
 * @method static \Lunar\Core\Models\Fulfilment ship(\Lunar\Core\Models\Contracts\Fulfilment $fulfilment, array $tracking = [])
 * @method static \Lunar\Core\Models\Fulfilment cancel(\Lunar\Core\Models\Contracts\Fulfilment $fulfilment)
 * @method static \Lunar\Core\Models\Fulfilment return(\Lunar\Core\Models\Contracts\Fulfilment $fulfilment)
 * @method static \Lunar\Core\Models\Fulfilment transition(\Lunar\Core\Models\Contracts\Fulfilment $fulfilment, string $state)
 * @method static \Lunar\Core\Models\Fulfilment changeLocation(\Lunar\Core\Models\Contracts\Fulfilment $fulfilment, int $locationId)
 * @method static \Lunar\Core\Models\FulfilmentTracking addTracking(\Lunar\Core\Models\Contracts\Fulfilment $fulfilment, array $attributes)
 * @method static void removeTracking(\Lunar\Core\Models\Contracts\FulfilmentTracking $tracking)
 * @method static \Lunar\Core\Models\Fulfilment hold(\Lunar\Core\Models\Contracts\Fulfilment $fulfilment, ?string $reason = null, ?string $note = null)
 * @method static \Lunar\Core\Models\Fulfilment release(\Lunar\Core\Models\Contracts\Fulfilment $fulfilment)
 *
 * @see FulfilmentManager
 */
class Fulfilments extends Facade
{
    protected static function getFacadeAccessor()
    {
        return FulfilmentManager::class;
    }
}
