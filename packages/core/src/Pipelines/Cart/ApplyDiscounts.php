<?php

namespace Lunar\Core\Pipelines\Cart;

use Closure;
use Illuminate\Support\Collection;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Facades\Discounts;
use Lunar\Core\Models\Cart;
use Lunar\Core\ValueObjects\Cart\Promotion;

class ApplyDiscounts
{
    /**
     * Called just before cart totals are calculated.
     *
     * @param  Closure(Cart):mixed  $next
     */
    public function handle(Cart $cart, Closure $next): mixed
    {
        /** @var Cart $cart */
        $cart->discounts = collect([]);
        $cart->discountBreakdown = collect([]);

        Discounts::apply($cart);

        $cart->promotions = $this->resolvePromotions($cart);

        return $next($cart);
    }

    /**
     * Build one promotion value object per distinct campaign behind the applied
     * discounts, summing what each campaign contributed. Standalone discounts
     * (no promotion) are skipped.
     *
     * @return Collection<int, Promotion>
     */
    protected function resolvePromotions(Cart $cart): Collection
    {
        return ($cart->discountBreakdown ?? collect())
            ->filter(fn ($breakdown) => $breakdown->discount->promotion_id)
            ->groupBy(fn ($breakdown) => $breakdown->discount->promotion_id)
            ->map(function ($breakdowns) {
                $promotion = $breakdowns->first()->discount->promotion;

                $amount = $breakdowns->reduce(
                    fn (?PriceValue $carry, $breakdown) => $carry ? $carry->add($breakdown->price) : $breakdown->price
                );

                $value = new Promotion;
                $value->reference = $promotion->handle;
                $value->description = (string) $promotion->translate('name');
                $value->amount = $amount;

                return $value;
            })->values();
    }
}
