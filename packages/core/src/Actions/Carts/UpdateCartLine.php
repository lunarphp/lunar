<?php

namespace Lunar\Core\Actions\Carts;

use Lunar\Core\Actions\AbstractAction;
use Lunar\Core\Contracts\Actions\Carts\UpdatesCartLine;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\CartLine;

class UpdateCartLine extends AbstractAction implements UpdatesCartLine
{
    /**
     * Execute the action.
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

            CartLine::whereId($cartLineId)->update($data);
        });
    }
}
