<?php

namespace Lunar\Actions\Carts;

use Illuminate\Support\Facades\DB;
use Lunar\Models\Cart;

class MergeCart
{
    /**
     * Execute the action.
     *
     * @param  \Lunar\Models\Cart  $target
     * @param  \Lunar\Models\Cart  $source
     * @return \Lunar\Models\Cart
     */
    public function execute(Cart $target, Cart $source)
    {
        if ($target->id == $source->id) {
            return $target;
        }

        DB::transaction(function () use ($target, $source) {
            $source->lines->map(function ($line) use ($target) {
                return [
                    'target' => $target->lines->first(function ($targetLine) use ($line) {
                        return $targetLine->purchasable_id == $line->purchasable_id &&
                        json_encode($targetLine->meta) == json_encode($line->meta);
                    }),
                    'source' => $line,
                ];
            })->each(function ($lines) use ($target) {
                // If no target, we are creating...
                if (empty($lines['target'])) {
                    $target->lines()->create([
                        'purchasable_id'   => $lines['source']->purchasable_id,
                        'purchasable_type' => $lines['source']->purchasable_type,
                        'quantity'         => $lines['source']->quantity,
                        'meta'             => $lines['source']->meta,
                    ]);

                    return;
                }

                $newMeta = $lines['target']->meta ?
                    array_merge((array) $lines['target']->meta, (array) $lines['source']->meta) :
                    $lines['source']->meta;

                $lines['target']->update([
                    'quantity' => $lines['target']->quantity + $lines['source']->quantity,
                    'meta'     => $newMeta,
                ]);
            });

            if ($source->user_id) {
                $target->update([
                    'user_id' => $source->user_id,
                ]);
            }

            $source->update([
                'merged_id' => $target->id,
            ]);
        });

        return $target;
    }
}
