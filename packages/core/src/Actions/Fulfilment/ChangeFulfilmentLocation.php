<?php

namespace Lunar\Core\Actions\Fulfilment;

use Lunar\Core\Contracts\Actions\Fulfilment\ChangesFulfilmentLocation;
use Lunar\Core\Exceptions\FulfilmentException;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Contracts\Fulfilment as FulfilmentContract;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\Location;

/**
 * Reassign a pre-ship fulfilment to a different location. A shipped or
 * returned fulfilment has already left a location, so its location is fixed.
 */
class ChangeFulfilmentLocation implements ChangesFulfilmentLocation
{
    /**
     * Fulfilment states whose location can still be changed.
     */
    public const RELOCATABLE_STATES = ['pending', 'in-progress'];

    public function execute(FulfilmentContract $fulfilment, int $locationId): Fulfilment
    {
        /** @var Fulfilment $fulfilment */
        if (! self::canRun($fulfilment)) {
            throw new FulfilmentException(__('lunar::exceptions.fulfilment_not_relocatable'));
        }

        if (! Location::query()->whereKey($locationId)->exists()) {
            throw new FulfilmentException(__('lunar::exceptions.fulfilment_location_not_found'));
        }

        return DB::transaction(function () use ($fulfilment, $locationId) {
            $fulfilment->update(['location_id' => $locationId]);

            return $fulfilment->refresh();
        });
    }

    /**
     * Whether the fulfilment's location can still be changed (pre-ship only).
     */
    public static function canRun(FulfilmentContract $fulfilment): bool
    {
        /** @var Fulfilment $fulfilment */
        return in_array($fulfilment->state::$name, self::RELOCATABLE_STATES, true);
    }
}
