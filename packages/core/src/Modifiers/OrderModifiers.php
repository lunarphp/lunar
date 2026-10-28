<?php

namespace Lunar\Core\Modifiers;

use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Order;

class OrderModifiers
{
    protected Collection $modifiers;

    public function __construct()
    {
        $this->modifiers = collect();
    }

    public function getModifiers()
    {
        return $this->modifiers;
    }

    public function add($modifier)
    {
        $this->modifiers->push($modifier);

        return $this;
    }

    /**
     * Run the creating hook, before the order creation pipeline starts.
     */
    public function creating(Cart $cart): Cart
    {
        return $this->run('creating', $cart);
    }

    /**
     * Run the created hook, once the order creation pipeline has finished.
     */
    public function created(Order $order): Order
    {
        return $this->run('created', $order);
    }

    /**
     * Send the subject through each modifier's hook method, in registration order.
     */
    protected function run(string $hook, mixed $subject): mixed
    {
        if ($this->modifiers->isEmpty()) {
            return $subject;
        }

        return app(Pipeline::class)
            ->send($subject)
            ->through($this->modifiers->all())
            ->via($hook)
            ->thenReturn();
    }
}
