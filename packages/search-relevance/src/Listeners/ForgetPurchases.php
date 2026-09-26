<?php

namespace Lunar\SearchRelevance\Listeners;

use Lunar\Core\Events\Orders\OrderCancelled;
use Lunar\Core\Events\Orders\OrderRefunded;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\ProductVariant;
use Lunar\SearchRelevance\Models\SearchEvent;

/**
 * A cancelled or refunded order no longer counts as proof the search worked:
 * drop the purchase events its attributed lines produced.
 */
class ForgetPurchases
{
    public function handle(OrderCancelled|OrderRefunded $event): void
    {
        $order = $event->order;
        $order->loadMissing('lines');

        $variantIds = $order->lines
            ->filter(fn (OrderLine $line) => $line->purchasable_type === ProductVariant::morphName() || $line->purchasable_type === ProductVariant::class)
            ->pluck('purchasable_id');

        $productIds = ProductVariant::query()->whereKey($variantIds)->pluck('product_id', 'id');

        foreach ($order->lines as $line) {
            $attribution = $line->meta['search_attribution'] ?? null;
            $productId = $productIds->get($line->purchasable_id);

            if (! is_array($attribution) || empty($attribution['search_id']) || $productId === null) {
                continue;
            }

            SearchEvent::query()
                ->where('search_id', (string) $attribution['search_id'])
                ->where('product_id', (int) $productId)
                ->where('type', 'purchase')
                ->delete();
        }
    }
}
