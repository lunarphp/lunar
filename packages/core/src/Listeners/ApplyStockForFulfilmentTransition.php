<?php

namespace Lunar\Core\Listeners;

use Lunar\Core\Enums\FulfilmentStateCategory;
use Lunar\Core\Enums\StockMovementType;
use Lunar\Core\Events\Fulfilment\FulfilmentStatusUpdated;
use Lunar\Core\Listeners\Concerns\SyncsTrackedStock;
use Lunar\Core\Models\ProductVariant;

/**
 * A fulfilment changed state → apply the physical `on_hand` movement (ship,
 * un-ship, return, undo-return) and resync committed stock.
 *
 * The physical ledger is variant-specific (`on_hand at a location`), so it only
 * fires for variant-backed lines; committed resync goes through the polymorphic
 * `TracksStock` seam. Cancellations carry no physical movement — they only
 * de-allocate, which the resync handles.
 */
class ApplyStockForFulfilmentTransition
{
    use SyncsTrackedStock;

    public function handle(FulfilmentStatusUpdated $event): void
    {
        $fulfilment = $event->fulfilment;
        $fulfilment->loadMissing(['location', 'order', 'lines.orderLine.purchasable']);

        if ($movement = $this->movementFor($event->previousStatus?->category(), $event->newStatus->category())) {
            [$type, $sign] = $movement;

            foreach ($fulfilment->lines as $line) {
                $variant = $line->orderLine?->purchasable;

                if ($variant instanceof ProductVariant) {
                    $variant->adjustStock($fulfilment->location, $sign * $line->quantity, $type, source: $fulfilment);
                }
            }
        }

        $this->syncTrackedStockForOrder($fulfilment->order);
    }

    /**
     * The signed `on_hand` movement a category transition implies, or null when
     * it carries none (e.g. pending to in-progress, or any move to cancelled).
     *
     * @return array{0: StockMovementType, 1: int}|null
     */
    private function movementFor(?FulfilmentStateCategory $from, FulfilmentStateCategory $to): ?array
    {
        return match (true) {
            $from === FulfilmentStateCategory::Outstanding && $to === FulfilmentStateCategory::Fulfilled => [StockMovementType::Shipped, -1],
            $from === FulfilmentStateCategory::Fulfilled && $to === FulfilmentStateCategory::Outstanding => [StockMovementType::Shipped, 1],
            $from === FulfilmentStateCategory::Fulfilled && $to === FulfilmentStateCategory::Returned => [StockMovementType::Returned, 1],
            $from === FulfilmentStateCategory::Returned && $to === FulfilmentStateCategory::Fulfilled => [StockMovementType::Returned, -1],
            default => null,
        };
    }
}
