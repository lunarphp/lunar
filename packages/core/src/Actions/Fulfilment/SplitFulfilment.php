<?php

namespace Lunar\Core\Actions\Fulfilment;

use Lunar\Core\Contracts\Actions\Fulfilment\SplitsFulfilment;
use Lunar\Core\Events\Fulfilment\FulfilmentCreated;
use Lunar\Core\Exceptions\FulfilmentException;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Contracts\Fulfilment as FulfilmentContract;
use Lunar\Core\Models\Fulfilment;

/**
 * Reorganise outstanding quantities of a pre-ship fulfilment into a new
 * parcel. Never changes how much is fulfilled — only how the outstanding
 * quantities are parcelled — so the rollups and headline are unaffected.
 */
class SplitFulfilment implements SplitsFulfilment
{
    /**
     * Fulfilment states that may be split — only outstanding (pre-ship)
     * quantity can be re-parcelled.
     */
    public const SPLITTABLE_STATES = ['pending', 'in-progress'];

    /**
     * @param  array<int|string, int>  $moves  [order_line_id => quantity]
     */
    public function execute(FulfilmentContract $fulfilment, array $moves): Fulfilment
    {
        /** @var Fulfilment $fulfilment */
        if (! self::canRun($fulfilment)) {
            throw new FulfilmentException(
                __('lunar::exceptions.fulfilment_not_splittable')
            );
        }

        return DB::transaction(function () use ($fulfilment, $moves) {
            // Lock the source lines so concurrent splits of the same parcel
            // serialise — otherwise both could read the same quantity and
            // together move out more than the line carries.
            $sourceLines = $fulfilment->lines()->lockForUpdate()->get()->keyBy('order_line_id');

            foreach ($moves as $orderLineId => $quantity) {
                $sourceLine = $sourceLines->get($orderLineId);

                if (! $sourceLine || $quantity < 1 || $quantity > $sourceLine->quantity) {
                    throw new FulfilmentException(
                        __('lunar::exceptions.fulfilment_split_quantity', [
                            'line' => $orderLineId,
                        ])
                    );
                }
            }

            // The split-off parcel inherits the source's state — splitting only
            // reorganises outstanding quantities, so a parcel that was already
            // being prepared (`in-progress`) shouldn't drop back to `pending`.
            /** @var Fulfilment $new */
            $new = $fulfilment->order->fulfilments()->create([
                'location_id' => $fulfilment->location_id,
                'state' => $fulfilment->state::$name,
            ]);

            foreach ($moves as $orderLineId => $quantity) {
                $sourceLine = $sourceLines->get($orderLineId);

                $remaining = $sourceLine->quantity - $quantity;

                if ($remaining <= 0) {
                    $sourceLine->delete();
                } else {
                    $sourceLine->update(['quantity' => $remaining]);
                }

                $new->lines()->create([
                    'order_line_id' => $orderLineId,
                    'quantity' => $quantity,
                ]);
            }

            FulfilmentCreated::dispatch($new);

            return $new->refresh();
        });
    }

    /**
     * Whether the fulfilment can be split — i.e. it is still pre-ship. Used to
     * gate the split action in the UI without catching an exception.
     */
    public static function canRun(FulfilmentContract $fulfilment): bool
    {
        /** @var Fulfilment $fulfilment */
        return in_array($fulfilment->state::$name, self::SPLITTABLE_STATES, true);
    }
}
