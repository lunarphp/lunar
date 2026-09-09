<?php

namespace Lunar\Core\Actions\Carts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Lunar\Core\Contracts\Actions\Carts\HydratesCartTotals;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Discount;
use Lunar\Core\ValueObjects\Cart\DiscountBreakdown;
use Lunar\Core\ValueObjects\Cart\DiscountBreakdownLine;
use Lunar\Core\ValueObjects\Cart\ShippingBreakdown;
use Lunar\Core\ValueObjects\Cart\TaxBreakdown;

/**
 * Rebuilds the calculated properties of a cart and its lines from the persisted
 * totals columns (spec 0076), so a hydrated cart is indistinguishable from a
 * freshly calculated one for everything the storefront renders.
 */
class HydrateCartTotals implements HydratesCartTotals
{
    public function execute(Cart $cart): Cart
    {
        /** @var Cart $cart */
        $cart->loadMissing(['currency', 'lines']);

        $currency = $cart->currency;

        $cart->subTotal = $this->price($cart, 'sub_total', $currency);
        $cart->subTotalDiscounted = $this->price($cart, 'sub_total_discounted', $currency);
        $cart->discountTotal = $this->price($cart, 'discount_total', $currency);
        $cart->shippingSubTotal = $this->price($cart, 'shipping_sub_total', $currency);
        $cart->shippingTaxTotal = $this->price($cart, 'shipping_tax_total', $currency);
        $cart->shippingTotal = $this->price($cart, 'shipping_total', $currency);
        $cart->taxTotal = $this->price($cart, 'tax_total', $currency);
        $cart->total = $this->price($cart, 'total', $currency);

        $cart->taxBreakdown = $this->hasRaw($cart, 'tax_breakdown')
            ? $cart->getAttribute('tax_breakdown')
            : new TaxBreakdown;

        $cart->shippingBreakdown = $this->hasRaw($cart, 'shipping_breakdown')
            ? $cart->getAttribute('shipping_breakdown')
            : new ShippingBreakdown;

        $cart->discountBreakdown = $this->discountBreakdown($cart, $currency);

        // The pipeline leaves the applied discount types on the cart; order
        // creation and DiscountManager::apply() read them from there.
        $cart->discounts = $cart->discountBreakdown->map(
            fn (DiscountBreakdown $breakdown) => $breakdown->discount->getType()
        );

        $cart->freeItems = $this->freeItems($cart);

        foreach ($cart->lines as $line) {
            $line->setRelation('cart', $cart);
            $this->hydrateLine($line, $currency);
        }

        return $cart->cacheProperties();
    }

    protected function hydrateLine(CartLine $line, Currency $currency): void
    {
        $line->unitPrice = $this->price($line, 'unit_price', $currency);
        $line->unitPriceInclTax = $this->price($line, 'unit_price_incl_tax', $currency);
        $line->subTotal = $this->price($line, 'sub_total', $currency);
        $line->subTotalDiscounted = $this->price($line, 'sub_total_discounted', $currency);
        $line->discountTotal = $this->price($line, 'discount_total', $currency);
        $line->taxAmount = $this->price($line, 'tax_total', $currency);
        $line->total = $this->price($line, 'total', $currency);
        $line->promotionDescription = (string) $line->getAttribute('promotion_description');
        $line->taxBreakdown = $this->hasRaw($line, 'tax_breakdown')
            ? $line->getAttribute('tax_breakdown')
            : new TaxBreakdown;

        $line->cacheProperties();
    }

    /**
     * @return Collection<int, DiscountBreakdown>
     */
    protected function discountBreakdown(Cart $cart, Currency $currency): Collection
    {
        $stored = collect($cart->getAttribute('discount_breakdown') ?? []);

        if ($stored->isEmpty()) {
            return collect();
        }

        $discounts = Discount::whereIn('id', $stored->pluck('discount_id')->filter()->unique())
            ->get()
            ->keyBy('id');

        $lines = $cart->lines->keyBy('id');

        return $stored->map(function (array $entry) use ($discounts, $lines, $currency) {
            $discount = $discounts->get($entry['discount_id'] ?? null);

            if (! $discount) {
                return null;
            }

            $breakdownLines = collect($entry['lines'] ?? [])
                ->map(function (array $breakdownLine) use ($lines) {
                    $line = $lines->get($breakdownLine['id'] ?? null);

                    return $line
                        ? new DiscountBreakdownLine(line: $line, quantity: (int) ($breakdownLine['qty'] ?? 0))
                        : null;
                })
                ->filter()
                ->values();

            return new DiscountBreakdown(
                price: new PriceValue((int) ($entry['total'] ?? 0), $currency),
                lines: $breakdownLines,
                discount: $discount,
            );
        })->filter()->values();
    }

    /**
     * Free items are stored as morph references; rebuild them with one query
     * per morph type, preserving the stored order.
     *
     * @return Collection<int, Model>
     */
    protected function freeItems(Cart $cart): Collection
    {
        $stored = collect($cart->getAttribute('free_items') ?? []);

        if ($stored->isEmpty()) {
            return collect();
        }

        $models = $stored->groupBy('type')->flatMap(function (Collection $references, string $type) {
            $class = Relation::getMorphedModel($type) ?? $type;

            if (! is_subclass_of($class, Model::class)) {
                return collect();
            }

            return $class::query()
                ->whereIn((new $class)->getKeyName(), $references->pluck('id'))
                ->get()
                ->keyBy(fn (Model $model) => $type.':'.$model->getKey());
        });

        return $stored
            ->map(fn (array $reference) => $models->get(($reference['type'] ?? '').':'.($reference['id'] ?? '')))
            ->filter()
            ->values();
    }

    protected function price(Model $model, string $column, Currency $currency): ?PriceValue
    {
        $value = $model->getAttribute($column);

        return $value === null ? null : new PriceValue((int) $value, $currency);
    }

    /**
     * The breakdown casts decode whatever they are given, so a NULL column is
     * checked on the raw attribute rather than passed through the cast.
     */
    protected function hasRaw(Model $model, string $column): bool
    {
        return ($model->getAttributes()[$column] ?? null) !== null;
    }
}
