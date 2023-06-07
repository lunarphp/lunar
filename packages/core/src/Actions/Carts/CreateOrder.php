<?php

namespace Lunar\Actions\Carts;

use Illuminate\Pipeline\Pipeline;
use Lunar\Actions\AbstractAction;
use Lunar\Exceptions\DisallowMultipleCartOrdersException;
use Lunar\Facades\DB;
use Lunar\Jobs\Orders\MarkAsNewCustomer;
use Lunar\Models\Cart;
use Lunar\Models\Order;
use Lunar\Pipelines\Order\Creation\CleanUpOrderLines;
use Lunar\Pipelines\Order\Creation\CreateOrderAddresses;
use Lunar\Pipelines\Order\Creation\CreateOrderLines;
use Lunar\Pipelines\Order\Creation\CreateShippingLine;
use Lunar\Pipelines\Order\Creation\FillOrderFromCart;
use Lunar\Pipelines\Order\Creation\MapDiscountBreakdown;

class CreateOrder extends AbstractAction
{
    /**
     * Execute the action.
     */
    final public function execute(
        Cart $cart,
        bool $allowMultipleOrders = false,
        int $orderIdToUpdate = null
    ): self {
        // Change relationship to order has the cart_id
        // $cart->draftOrder
        //
        // $cart->createOrder();
        // $cart->syncToOrder(1)
        // $cart->syncToOrder();
        // Fingerprint on checkout (Inertia)

        // You can't update the order unless it's a draft.
        $this->passThrough = DB::transaction(function () use ($cart, $allowMultipleOrders, $orderIdToUpdate) {
            $order = $cart->draftOrder($orderIdToUpdate)->first() ?: new Order;

            if ($cart->hasCompletedOrders() && ! $allowMultipleOrders) {
                throw new DisallowMultipleCartOrdersException;
            }

            $order->fill([
                'cart_id' => $cart->id,
            ]);

            $order = app(Pipeline::class)
                ->send($order)
                ->through([
                    FillOrderFromCart::class,
                    CreateOrderLines::class,
                    CreateOrderAddresses::class,
                    CreateShippingLine::class,
                    CleanUpOrderLines::class,
                    MapDiscountBreakdown::class,
                ])->thenReturn(function ($order) {
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

        return $this;
    }
}
