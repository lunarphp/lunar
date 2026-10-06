<?php

namespace Lunar\Core\Actions\Carts;

use Lunar\Core\Contracts\Actions\Carts\UpdatesCartLine;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\CartLine;

class UpdateCartLine implements UpdatesCartLine
{
    /**
     * Execute the action.
     *
     * A model save rather than a query-builder update, so the line observer
     * runs: the purchasable check on `updating` and the totals invalidation
     * on `saved`, as add() has always had.
     */
    public function execute(
        int $cartLineId,
        int $quantity,
        ?array $meta = null
    ): void {
        DB::transaction(function () use ($cartLineId, $quantity, $meta) {
            $data = [
                'quantity' => $quantity,
            ];

            if ($meta) {
                $data['meta'] = $meta;
            }

            CartLine::query()->find($cartLineId)?->update($data);
        });
    }
}
