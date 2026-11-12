<?php

namespace Lunar\Core\Listeners;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Collection;
use Lunar\Core\Contracts\Actions\Discounts\CountsCustomerRedemptions;
use Lunar\Core\Contracts\Actions\Discounts\ResolvesCustomerEmail;
use Lunar\Core\Enums\DiscountLimit;
use Lunar\Core\Events\Discounts\DiscountLimitExceeded;
use Lunar\Core\Events\Orders\OrderPlaced;
use Lunar\Core\Models\Cart;
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
        protected ResolvesCustomerEmail $resolvesCustomerEmail,
        protected CountsCustomerRedemptions $countsCustomerRedemptions,
    ) {}

    public function handle(OrderPlaced $event): void
    {
        $order = $event->order;

        $discountIds = $this->discountIds($order);

        if ($discountIds->isEmpty()) {
            return;
        }

        $cart = $order->cart;
        $email = $cart ? $this->resolvesCustomerEmail->execute($cart) : null;

        $order->getConnection()->transaction(function () use ($order, $cart, $email, $discountIds) {
            foreach (Discount::query()->whereIn('id', $discountIds)->get() as $discount) {
                $this->record($order, $cart, $email, $discount);
            }
        });
    }

    protected function record(Order $order, ?Cart $cart, ?string $email, Discount $discount): void
    {
        $inserted = DiscountRedemption::query()->insertOrIgnore([
            'discount_id' => $discount->id,
            'order_id' => $order->id,
            'cart_id' => $order->cart_id,
            'user_id' => $order->user_id,
            'email' => $email,
            'redeemed_at' => $order->placed_at,
        ]);

        if (! $inserted) {
            return;
        }

        Discount::query()->whereKey($discount->id)->increment('uses');

        $discount->refresh();

        if ($discount->max_uses !== null && $discount->uses > $discount->max_uses) {
            $this->reportExceeded($order, $discount, DiscountLimit::MaxUses, $discount->uses, $discount->max_uses);
        }

        if ($cart && $discount->max_uses_per_user) {
            $customerUses = $this->countsCustomerRedemptions->execute($discount, $cart);

            if ($customerUses > $discount->max_uses_per_user) {
                $this->reportExceeded($order, $discount, DiscountLimit::MaxUsesPerUser, $customerUses, $discount->max_uses_per_user);
            }
        }
    }

    protected function reportExceeded(Order $order, Discount $discount, DiscountLimit $limit, int $uses, int $max): void
    {
        $this->events->dispatch(new DiscountLimitExceeded($order, $discount, $limit));

        activity()
            ->performedOn($order)
            ->event('discount-limit-exceeded')
            ->withProperties([
                'discount_id' => $discount->id,
                'discount' => $discount->name,
                'coupon' => null,
                'limit' => $limit->value,
                'uses' => $uses,
                'max_uses' => $max,
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
