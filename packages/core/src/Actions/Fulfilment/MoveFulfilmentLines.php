<?php

namespace Lunar\Core\Actions\Fulfilment;

use Lunar\Core\Contracts\Actions\Fulfilment\MovesFulfilmentLines;
use Lunar\Core\Exceptions\FulfilmentException;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Fulfilment;

/**
 * Move selected line quantities from one pre-ship fulfilment into another on
 * the same order (the Shopify "merge selected items into another fulfilment"
 * operation). Preserves total fulfilled quantity, so the rollups are
 * untouched; the source is deleted if it ends up empty.
 */
class MoveFulfilmentLines implements MovesFulfilmentLines
{
    /**
     * @param  array<int|string, int>  $moves  [order_line_id => quantity]
     */
    public function execute(Fulfilment $from, Fulfilment $to, array $moves): Fulfilment
    {
        /** @var Fulfilment $from */
        /** @var Fulfilment $to */
        $this->guard($from, $to);

        return DB::transaction(function () use ($from, $to, $moves) {
            foreach ($moves as $orderLineId => $quantity) {
                $quantity = (int) $quantity;

                if ($quantity < 1) {
                    continue;
                }

                // Locked reads: both sides are read-modify-write, so concurrent
                // moves touching the same lines must serialise.
                $sourceLine = $from->lines()->where('order_line_id', $orderLineId)->lockForUpdate()->first();

                if (! $sourceLine || $quantity > $sourceLine->quantity) {
                    throw new FulfilmentException(
                        __('lunar::exceptions.fulfilment_split_quantity', ['line' => $orderLineId])
                    );
                }

                $remaining = $sourceLine->quantity - $quantity;

                if ($remaining <= 0) {
                    $sourceLine->delete();
                } else {
                    $sourceLine->update(['quantity' => $remaining]);
                }

                $targetLine = $to->lines()->where('order_line_id', $orderLineId)->lockForUpdate()->first();

                if ($targetLine) {
                    $targetLine->update(['quantity' => $targetLine->quantity + $quantity]);
                } else {
                    $to->lines()->create(['order_line_id' => $orderLineId, 'quantity' => $quantity]);
                }
            }

            // A fulfilment emptied by the move is no longer meaningful.
            if ($from->lines()->count() === 0) {
                $from->delete();
            }

            return $to->refresh();
        });
    }

    /**
     * @throws FulfilmentException
     */
    protected function guard(Fulfilment $from, Fulfilment $to): void
    {
        if ($from->getKey() === $to->getKey()) {
            throw new FulfilmentException(__('lunar::exceptions.fulfilment_merge_target_in_sources'));
        }

        if ($from->order_id !== $to->order_id) {
            throw new FulfilmentException(__('lunar::exceptions.fulfilment_merge_different_orders'));
        }

        if ($from->location_id !== $to->location_id) {
            throw new FulfilmentException(__('lunar::exceptions.fulfilment_merge_different_locations'));
        }

        if ($from->method !== $to->method) {
            throw new FulfilmentException(__('lunar::exceptions.fulfilment_method_mismatch'));
        }

        foreach ([$from, $to] as $fulfilment) {
            if (! MergeFulfilments::isMergeable($fulfilment)) {
                throw new FulfilmentException(__('lunar::exceptions.fulfilment_not_mergeable'));
            }
        }
    }
}
