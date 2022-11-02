<?php

namespace Lunar\Actions\Carts;

use Illuminate\Support\Facades\DB;
use Lunar\Actions\AbstractAction;
use Lunar\Models\CartLine;

class UpdateCartLine extends AbstractAction
{
    /**
     * Execute the action.
     *
     * @param  \Lunar\Models\CartLine  $cartLine
     * @param  \Illuminate\Database\Eloquent\Collection  $customerGroups
     * @return \Lunar\Models\CartLine
     */
    public function execute(
        int $cartLineId,
        int $quantity,
        array $meta = null
    ): self {
        DB::transaction(function () use ($cartLineId, $quantity, $meta) {
            $data = [
                'quantity' => $quantity,
            ];

            if ($meta) {
                $data['meta'] = $meta;
            }

            CartLine::whereId($cartLineId)->update($data);
        });
        return $this;
    }
}
