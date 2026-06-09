<?php

namespace Lunar\Core\Actions\Carts;

use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\App;
use Lunar\Core\Contracts\Actions\Carts\CreatesOrder;
use Lunar\Core\Exceptions\DisallowMultipleCartOrdersException;
use Lunar\Core\Facades\DB;
use Lunar\Core\Jobs\Orders\MarkAsNewCustomer;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Contracts\Cart as CartContract;
use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Order;

class CreateOrder implements CreatesOrder
{
    /**
     * Execute the action.
     */
    public function execute(
        CartContract $cart,
        bool $allowMultipleOrders = false,
        ?int $orderIdToUpdate = null
    ): OrderContract {
        return DB::transaction(function () use ($cart, $allowMultipleOrders, $orderIdToUpdate) {
            /** @var Order $order */
            /** @var Cart $cart */
            $order = $cart->draftOrder($orderIdToUpdate)->first() ?: App::make(OrderContract::class);

            if ($cart->hasCompletedOrders() && ! $allowMultipleOrders) {
                throw new DisallowMultipleCartOrdersException;
            }

            $order->fill([
                'cart_id' => $cart->id,
                'fingerprint' => $cart->fingerprint(),
            ]);

            $order = app(Pipeline::class)
                ->send($order)
                ->through(
                    config('lunar.orders.pipelines.creation', [])
                )->thenReturn(function ($order) {
                    return $order;
                });

            $cart->discounts?->each(function ($discount) use ($cart) {
                $discount->markAsUsed($cart)->discount->save();
            });

            $cart->save();

            MarkAsNewCustomer::dispatch($order->id);

            $order->refresh();

            return $order;
        });
    }
}
