<?php

namespace Lunar\Core\Modifiers;

use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Lunar\Core\Models\Cart;

class CartModifiers
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
    }

    public function remove($modifier)
    {
        $this->modifiers->forget($modifier);
    }

    /**
     * Run the calculating hook, before the cart pipeline starts.
     */
    public function calculating(Cart $cart): Cart
    {
        return $this->run('calculating', $cart);
    }

    /**
     * Run the calculated hook, once the cart pipeline and every line's calculated hook have run.
     */
    public function calculated(Cart $cart): Cart
    {
        return $this->run('calculated', $cart);
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
