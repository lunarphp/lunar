<?php

namespace Lunar\Core\Actions\Carts;

use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\App;
use Lunar\Core\Contracts\Actions\Carts\CreatesOrder;
use Lunar\Core\Exceptions\DisallowMultipleCartOrdersException;
use Lunar\Core\Facades\DB;
use Lunar\Core\Jobs\Orders\MarkAsNewCustomer;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Order;
use Lunar\Core\Modifiers\OrderModifiers;

class CreateOrder implements CreatesOrder
{
    public function __construct(
        protected OrderModifiers $orderModifiers,
    ) {}

    /**
     * Execute the action.
     */
    public function execute(
        Cart $cart,
        bool $allowMultipleOrders = false,
        ?int $orderIdToUpdate = null
    ): Order {
        return DB::transaction(function () use ($cart, $allowMultipleOrders, $orderIdToUpdate) {
            /** @var Order $order */
            /** @var Cart $cart */
            $order = $cart->draftOrder($orderIdToUpdate)->first() ?: App::make(Order::class);

            // Read before the creation pipeline runs: MapDiscountBreakdown
            // rewrites the order's breakdown, so afterwards every discount would
            // look as though it had already been consumed.
            $alreadyConsumed = $cart->consumedDiscountIds();

            if ($cart->hasCompletedOrders() && ! $allowMultipleOrders) {
                throw new DisallowMultipleCartOrdersException;
            }

            $cart = $this->orderModifiers->creating($cart);

            $order->fill([
                'cart_id' => $cart->id,
                'fingerprint' => $cart->fingerprint(),
            ]);

            // The pipeline reads $order->cart; hand it this instance so the creating hook's changes reach it.
            $order->setRelation('cart', $cart);

            $order = app(Pipeline::class)
                ->send($order)
                ->through(
                    config('lunar.orders.pipelines.creation', [])
                )->thenReturn(function ($order) {
                    return $order;
                });

            // Creating the order again for the same cart — a declined card and a
            // retry — must not consume a second use of the same discount.
            $cart->discounts?->each(function ($discount) use ($cart, $alreadyConsumed) {
                if ($alreadyConsumed->contains($discount->discount->id)) {
                    return;
                }

                $discount->markAsUsed($cart)->discount->save();
            });

            // The breakdown has been rewritten, so anything still holding this
            // cart must not read a set memoised before the order existed.
            $cart->forgetConsumedDiscountIds();

            $cart->save();

            MarkAsNewCustomer::dispatch($order->id);

            $order->refresh();

            return $this->orderModifiers->created($order);
        });
    }
}
