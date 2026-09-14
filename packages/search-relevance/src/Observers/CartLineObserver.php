<?php

namespace Lunar\SearchRelevance\Observers;

use Illuminate\Contracts\Bus\Dispatcher;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\ProductVariant;
use Lunar\SearchRelevance\Support\Attribution;
use Lunar\SearchRelevance\Jobs\RecordEvent;

/** CartLine `created` observer: credits the line to the search its product was clicked from. */
class CartLineObserver
{
    public function __construct(
        protected Attribution $attribution,
        protected Dispatcher $bus,
    ) {}

    public function created(CartLine $line): void
    {
        $productId = $this->productId($line);

        if ($productId === null) {
            return;
        }

        $attribution = $this->attribution->find($productId);

        if (! $attribution) {
            return;
        }

        $meta = $line->meta?->getArrayCopy() ?? [];
        $meta['search_attribution'] = [
            'search_id' => $attribution['search_id'],
            'position' => $attribution['position'],
            'source' => $attribution['source'],
            'session_id' => $attribution['session_id'],
        ];
        $line->meta = $meta;
        $line->saveQuietly();

        $this->bus->dispatch(new RecordEvent(
            $attribution['search_id'],
            $productId,
            (int) $attribution['position'],
            'basket',
            $attribution['source'],
            $attribution['session_id'],
        ));
    }

    protected function productId(CartLine $line): ?int
    {
        if ($line->purchasable_type !== ProductVariant::morphName() && $line->purchasable_type !== ProductVariant::class) {
            return null;
        }

        return ProductVariant::query()->whereKey($line->purchasable_id)->value('product_id');
    }
}
