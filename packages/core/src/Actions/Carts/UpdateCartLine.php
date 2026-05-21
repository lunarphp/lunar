<?php

namespace Lunar\Core\Actions\Carts;

use Lunar\Core\Actions\AbstractAction;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\CartLine;

class UpdateCartLine extends AbstractAction
{
    /**
     * Execute the action.
     */
    public function execute(
        int $cartLineId,
        int $quantity,
        $meta = null
    ): self {
        DB::transaction(function () use ($cartLineId, $quantity, $meta) {
            $data = [
                'quantity' => $quantity,
            ];

            if ($meta) {
                if (is_object($meta)) {
                    $meta = (array) $meta;
                }
                $data['meta'] = $meta;
            }

            CartLine::whereId($cartLineId)->update($data);
        });

        return $this;
    }
}
