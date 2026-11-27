<?php

namespace Lunar\Core\Actions\Discounts;

use Lunar\Core\Contracts\Actions\Discounts\ReconcilesRewardLines;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartLine;
use Lunar\Core\ValueObjects\Cart\DiscountBreakdownLine;

/**
 * A line a discount added holds exactly the reward units that discount
 * allocated to it in the current calculation (spec remove-unearned-reward-lines).
 *
 * The target for each line is read from the cart's discount breakdown: the
 * quantities recorded against the line by the discount named in its
 * `added_by_discount_id`. A discount that no longer applies records nothing,
 * so its lines go. Unsaved lines a discount put in its breakdown are created.
 *
 * Runs while the cart is calculating, so the line observer leaves the totals
 * the calculation is about to persist alone.
 */
class ReconcileRewardLines implements ReconcilesRewardLines
{
    public function execute(Cart $cart): bool
    {
        /** @var array<int, array{line: CartLine, quantity: int}> $targets keyed by spl_object_id() */
        $targets = [];

        foreach ($cart->discountBreakdown ?? [] as $breakdown) {
            foreach ($breakdown->lines as $breakdownLine) {
                // `lines` is an untyped collection a custom discount type fills.
                if (! $breakdownLine instanceof DiscountBreakdownLine) {
                    continue;
                }

                $line = $breakdownLine->line;

                if ($line->added_by_discount_id === null || $line->added_by_discount_id != $breakdown->discount->id) {
                    continue;
                }

                $targets[spl_object_id($line)] ??= ['line' => $line, 'quantity' => 0];
                $targets[spl_object_id($line)]['quantity'] += $breakdownLine->quantity;
            }
        }

        $deleted = [];
        $created = [];
        $resized = false;

        foreach ($cart->lines->filter(fn (CartLine $line) => $line->isAddedByDiscount()) as $line) {
            $target = $targets[spl_object_id($line)]['quantity'] ?? 0;

            unset($targets[spl_object_id($line)]);

            if ($target < 1) {
                $line->delete();
                $deleted[] = $line;

                continue;
            }

            if ($line->quantity !== $target) {
                $line->quantity = $target;
                $line->save();
                $resized = true;
            }
        }

        foreach ($targets as ['line' => $line, 'quantity' => $target]) {
            if ($line->exists || $target < 1) {
                continue;
            }

            $line->quantity = $target;
            $line->save();
            $created[] = $line;
        }

        if ($deleted || $created) {
            $cart->setRelation('lines', $cart->lines
                ->reject(fn (CartLine $line) => in_array($line, $deleted, true))
                ->concat($created)
                ->values());
        }

        return $deleted || $created || $resized;
    }
}
