<?php

namespace Lunar\Core\Actions\Fulfilment;

use Lunar\Core\Contracts\Actions\Fulfilment\SplitsFulfilment;
use Lunar\Core\Enums\FulfilmentStateCategory;
use Lunar\Core\Events\Fulfilment\FulfilmentCreated;
use Lunar\Core\Exceptions\FulfilmentException;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Fulfilment;

/**
 * Reorganise outstanding quantities of a fulfilment into a new fulfilment. Never
 * changes how much is fulfilled — only how the outstanding quantities are
 * split — so the rollups and headline are unaffected.
 */
class SplitFulfilment implements SplitsFulfilment
{
    /**
     * @param  array<int|string, int>  $moves  [order_line_id => quantity]
     */
    public function execute(Fulfilment $fulfilment, array $moves): Fulfilment
    {
        /** @var Fulfilment $fulfilment */
        if (! self::canRun($fulfilment)) {
            throw new FulfilmentException(
                __('lunar::exceptions.fulfilment_not_splittable')
            );
        }

        return DB::transaction(function () use ($fulfilment, $moves) {
            // Lock the source lines so concurrent splits of the same fulfilment
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

            // The split-off fulfilment inherits the source's method and state —
            // splitting only reorganises outstanding quantities, so a fulfilment
            // already being prepared shouldn't drop back to its default state,
            // and a collection/digital fulfilment must stay that method.
            /** @var Fulfilment $new */
            $new = $fulfilment->order->fulfilments()->create([
                'location_id' => $fulfilment->location_id,
                'method' => $fulfilment->method,
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
     * Whether the fulfilment can be split — i.e. it still has outstanding
     * (un-handed-over) quantity. Used to gate the split action in the UI
     * without catching an exception.
     */
    public static function canRun(Fulfilment $fulfilment): bool
    {
        /** @var Fulfilment $fulfilment */
        return $fulfilment->state->category() === FulfilmentStateCategory::Outstanding;
    }
}
