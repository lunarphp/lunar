<?php

namespace Lunar\Core\Listeners;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Collection;
use Lunar\Core\Events\Discounts\DiscountLimitExceeded;
use Lunar\Core\Events\Orders\OrderPlaced;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\DiscountRedemption;
use Lunar\Core\Models\Order;

/**
 * Record a use of every discount on a placed order. Idempotent per order and
 * discount, so `OrderPlaced` firing again counts nothing twice. Never rejects:
 * the order has been paid for, so going over a limit is reported, not undone.
 */
class RecordDiscountRedemptions
{
    public function __construct(
        protected Dispatcher $events,
    ) {}

    public function handle(OrderPlaced $event): void
    {
        $order = $event->order;

        $discountIds = $this->discountIds($order);

        if ($discountIds->isEmpty()) {
            return;
        }

        $order->getConnection()->transaction(function () use ($order, $discountIds) {
            foreach (Discount::query()->whereIn('id', $discountIds)->get() as $discount) {
                $this->record($order, $discount);
            }
        });
    }

    protected function record(Order $order, Discount $discount): void
    {
        $inserted = DiscountRedemption::query()->insertOrIgnore([
            'discount_id' => $discount->id,
            'order_id' => $order->id,
            'cart_id' => $order->cart_id,
            'user_id' => $order->user_id,
            'redeemed_at' => $order->placed_at,
        ]);

        if (! $inserted) {
            return;
        }

        Discount::query()->whereKey($discount->id)->increment('uses');

        $discount->refresh();

        if ($discount->max_uses === null || $discount->uses <= $discount->max_uses) {
            return;
        }

        $this->events->dispatch(new DiscountLimitExceeded($order, $discount));

        activity()
            ->performedOn($order)
            ->event('discount-limit-exceeded')
            ->withProperties([
                'discount_id' => $discount->id,
                'discount' => $discount->name,
                'coupon' => null,
                'uses' => $discount->uses,
                'max_uses' => $discount->max_uses,
            ])
            ->log('discount-limit-exceeded');
    }

    /**
     * @return Collection<int, int>
     */
    protected function discountIds(Order $order): Collection
    {
        // The raw column: the cast hydrates an OrderLine per breakdown line just to reach an id.
        $breakdown = $order->getAttributes()['discount_breakdown'] ?? null;

        return collect(json_decode($breakdown ?: '[]', true) ?: [])
            ->pluck('discount_id')
            ->filter()
            ->unique()
            ->values();
    }
}
