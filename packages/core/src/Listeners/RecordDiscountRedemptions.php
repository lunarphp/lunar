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
use Lunar\Core\Models\Coupon;
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

        $entries = $this->breakdownEntries($order);

        if ($entries->isEmpty()) {
            return;
        }

        $cart = $order->cart;
        $email = $cart ? $this->resolvesCustomerEmail->execute($cart) : null;

        $order->getConnection()->transaction(function () use ($order, $cart, $email, $entries) {
            $discounts = Discount::query()->whereIn('id', $entries->keys())->get();
            $coupons = Coupon::query()->whereIn('id', $entries->filter())->get()->keyBy('id');

            foreach ($discounts as $discount) {
                $couponId = $entries->get($discount->id);

                $this->record($order, $cart, $email, $discount, $couponId ? $coupons->get($couponId) : null);
            }
        });
    }

    protected function record(Order $order, ?Cart $cart, ?string $email, Discount $discount, ?Coupon $coupon): void
    {
        $inserted = DiscountRedemption::query()->insertOrIgnore([
            'discount_id' => $discount->id,
            'coupon_id' => $coupon?->id,
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
            $this->reportExceeded($order, $discount, $coupon, DiscountLimit::MaxUses, $discount->uses, $discount->max_uses);
        }

        if ($coupon) {
            Coupon::query()->whereKey($coupon->id)->increment('uses');

            $coupon->refresh();

            if ($coupon->max_uses !== null && $coupon->uses > $coupon->max_uses) {
                $this->reportExceeded($order, $discount, $coupon, DiscountLimit::CouponMaxUses, $coupon->uses, $coupon->max_uses);
            }
        }

        if ($cart && $discount->max_uses_per_user) {
            $customerUses = $this->countsCustomerRedemptions->execute($discount, $cart);

            if ($customerUses > $discount->max_uses_per_user) {
                $this->reportExceeded($order, $discount, $coupon, DiscountLimit::MaxUsesPerUser, $customerUses, $discount->max_uses_per_user);
            }
        }
    }

    protected function reportExceeded(Order $order, Discount $discount, ?Coupon $coupon, DiscountLimit $limit, int $uses, int $max): void
    {
        $this->events->dispatch(new DiscountLimitExceeded($order, $discount, $limit, $coupon));

        activity()
            ->performedOn($order)
            ->event('discount-limit-exceeded')
            ->withProperties([
                'discount_id' => $discount->id,
                'discount' => $discount->name,
                'coupon' => $coupon?->code,
                'limit' => $limit->value,
                'uses' => $uses,
                'max_uses' => $max,
            ])
            ->log('discount-limit-exceeded');
    }

    /**
     * The coupon id each discount on the order was redeemed with, keyed by discount id.
     *
     * @return Collection<int, ?int>
     */
    protected function breakdownEntries(Order $order): Collection
    {
        // The raw column: the cast hydrates an OrderLine per breakdown line just to reach an id.
        $breakdown = $order->getAttributes()['discount_breakdown'] ?? null;

        return collect(json_decode($breakdown ?: '[]', true) ?: [])
            ->filter(fn (array $entry) => filled($entry['discount_id'] ?? null))
            ->mapWithKeys(fn (array $entry) => [(int) $entry['discount_id'] => $entry['coupon_id'] ?? null]);
    }
}
