<?php

namespace Lunar\SearchRelevance\Listeners;

use Illuminate\Contracts\Bus\Dispatcher;
use Lunar\Core\Events\Orders\OrderPlaced;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\ProductVariant;
use Lunar\SearchRelevance\Jobs\RecordEvent;

/** Turns the attribution copied from cart lines into purchase events once the order is placed. */
class AttributeOrderLines
{
    public function __construct(protected Dispatcher $bus) {}

    public function handle(OrderPlaced $event): void
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

            $sessionId = $attribution['session_id'] ?? ($order->cart_id ? 'cart:'.$order->cart_id : 'session:none');

            $this->bus->dispatch(new RecordEvent(
                (string) $attribution['search_id'],
                (int) $productId,
                (int) ($attribution['position'] ?? 0),
                'purchase',
                (string) ($attribution['source'] ?? 'organic'),
                (string) $sessionId,
            ));
        }
    }
}
