<?php

namespace Lunar\Core\Actions\Carts;

use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Lunar\Core\Contracts\Actions\Carts\PersistsCartTotals;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartLine;
use Lunar\Core\ValueObjects\Cart\DiscountBreakdownLine;

/**
 * Writes a calculated cart's totals to the cart and line rows (spec 0076).
 *
 * The cart write is a single query-builder UPDATE guarded on the revision the
 * cart was loaded with, which is also the value written to
 * `calculated_revision`. Zero rows affected means another request changed the
 * cart while this one was calculating: the snapshot is skipped, the in-memory
 * totals stay valid for this request, and the next request recalculates. No
 * `save()`, so no model events, no activity log and no `updated_at` bump.
 */
class PersistCartTotals implements PersistsCartTotals
{
    public function __construct(
        protected ConnectionResolverInterface $connections,
    ) {}

    public function execute(Cart $cart): bool
    {
        /** @var Cart $cart */
        if (! $cart->exists || $cart->total === null) {
            return false;
        }

        $revision = (int) $cart->revision;

        $values = [
            'sub_total' => $cart->subTotal?->value,
            'sub_total_discounted' => $cart->subTotalDiscounted?->value,
            'discount_total' => $cart->discountTotal?->value,
            'shipping_sub_total' => $cart->shippingSubTotal?->value,
            'shipping_tax_total' => $cart->shippingTaxTotal?->value,
            'shipping_total' => $cart->shippingTotal?->value,
            'tax_total' => $cart->taxTotal?->value,
            'total' => $cart->total->value,
            'tax_breakdown' => $cart->taxBreakdown,
            'shipping_breakdown' => $cart->shippingBreakdown,
            'discount_breakdown' => $this->discountBreakdown($cart),
            'free_items' => $this->freeItems($cart),
            'calculated_revision' => $revision,
            'calculated_at' => now(),
        ];

        $lines = $cart->lines
            ->filter(fn (CartLine $line) => $line->exists && $line->total !== null)
            ->mapWithKeys(fn (CartLine $line) => [$line->getKey() => [
                'unit_price' => $line->unitPrice?->value,
                'unit_price_incl_tax' => $line->unitPriceInclTax?->value,
                'sub_total' => $line->subTotal?->value,
                'sub_total_discounted' => $line->subTotalDiscounted?->value,
                'discount_total' => $line->discountTotal?->value,
                'tax_total' => $line->taxAmount?->value,
                'total' => $line->total->value,
                'tax_breakdown' => $line->taxBreakdown,
                'promotion_description' => $line->promotionDescription ?: null,
            ]]);

        $written = $this->connections->connection($cart->getConnectionName())->transaction(function () use ($cart, $revision, $values, $lines) {
            $affected = $cart->newModelQuery()
                ->whereKey($cart->getKey())
                ->where('revision', $revision)
                ->toBase()
                ->update($this->raw($cart, $values));

            // MySQL reports changed rows, not matched rows, so an identical
            // re-write inside the same second looks like a failed guard.
            if ($affected === 0 && ! $this->guardStillHolds($cart, $revision)) {
                return false;
            }

            // One UPDATE per line rather than an upsert: a line removed by
            // another request between the guard and this write must not be
            // resurrected.
            foreach ($cart->lines as $line) {
                if (! $lines->has($line->getKey())) {
                    continue;
                }

                $line->newModelQuery()
                    ->whereKey($line->getKey())
                    ->toBase()
                    ->update($this->raw($line, $lines->get($line->getKey())));
            }

            return true;
        });

        if (! $written) {
            return false;
        }

        $this->sync($cart, $values);

        foreach ($cart->lines as $line) {
            if ($lines->has($line->getKey())) {
                $this->sync($line, $lines->get($line->getKey()));
            }
        }

        return true;
    }

    protected function guardStillHolds(Cart $cart, int $revision): bool
    {
        $current = $cart->newModelQuery()->whereKey($cart->getKey())->toBase()->value('revision');

        return $current !== null && (int) $current === $revision;
    }

    /**
     * Storage values for the columns, run through the model's casts on a copy
     * so the in-memory model is untouched until the guard has passed.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    protected function raw(Model $model, array $values): array
    {
        $copy = (clone $model)->forceFill($values);

        return Arr::only($copy->getAttributes(), array_keys($values))
            + array_fill_keys(array_keys($values), null);
    }

    /**
     * Mirror the written values into the in-memory model's attributes and
     * originals so it reads as persisted without a reload.
     *
     * @param  array<string, mixed>  $values
     */
    protected function sync(Model $model, array $values): void
    {
        $raw = $this->raw($model, $values);

        $model->setRawAttributes(array_merge($model->getAttributes(), $raw));
        $model->syncOriginalAttributes(array_keys($raw));
    }

    /**
     * A breakdown's lines are DiscountBreakdownLine objects from the shipped
     * discount types; a consumer type that pushes the CartLine itself is
     * tolerated and stored at the line's quantity.
     *
     * @return array<int, array{discount_id: int, total: int, lines: array<int, array{id: int, qty: int}>}>
     */
    protected function discountBreakdown(Cart $cart): array
    {
        return ($cart->discountBreakdown ?? collect())->map(fn ($breakdown) => [
            'discount_id' => $breakdown->discount->getKey(),
            'total' => $breakdown->price->value,
            'lines' => collect($breakdown->lines)->map(function ($line) {
                if ($line instanceof DiscountBreakdownLine) {
                    return ['id' => $line->line->getKey(), 'qty' => $line->quantity];
                }

                if ($line instanceof CartLine) {
                    return ['id' => $line->getKey(), 'qty' => $line->quantity];
                }

                return null;
            })->filter()->values()->all(),
        ])->values()->all();
    }

    /**
     * Morph references of the purchasables in `$cart->freeItems`; anything
     * that is not a model is not representable in the snapshot and is dropped.
     *
     * @return array<int, array{type: string, id: int|string}>
     */
    protected function freeItems(Cart $cart): array
    {
        return ($cart->freeItems ?? collect())
            ->filter(fn ($item) => $item instanceof Model)
            ->map(fn (Model $item) => [
                'type' => $item->getMorphClass(),
                'id' => $item->getKey(),
            ])->values()->all();
    }
}
