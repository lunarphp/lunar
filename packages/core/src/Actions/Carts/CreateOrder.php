<?php

namespace Lunar\Core\Actions\Carts;

use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\App;
use Lunar\Core\Contracts\Actions\Carts\CreatesOrder;
use Lunar\Core\Contracts\StepManifest;
use Lunar\Core\Enums\StepList;
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
        protected StepManifest $steps,
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
                ->through($this->steps->for(StepList::OrderCreation)->all())
                ->thenReturn(function ($order) {
                    return $order;
                });

            $cart->save();

            MarkAsNewCustomer::dispatch($order->id);

            $order->refresh();

            return $this->orderModifiers->created($order);
        });
    }
}
