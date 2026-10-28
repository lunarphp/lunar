<?php

namespace Lunar\Core\Modifiers;

use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Lunar\Core\Models\CartLine;

class CartLineModifiers
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
     * Run the calculating hook, before the cart_lines pipeline prices the line.
     */
    public function calculating(CartLine $cartLine): CartLine
    {
        return $this->run('calculating', $cartLine);
    }

    /**
     * Run the subtotalled hook, once the line's unit price and subtotal are set.
     */
    public function subtotalled(CartLine $cartLine): CartLine
    {
        return $this->run('subtotalled', $cartLine);
    }

    /**
     * Run the calculated hook, once the cart pipeline has finished and the line's totals are final.
     */
    public function calculated(CartLine $cartLine): CartLine
    {
        return $this->run('calculated', $cartLine);
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
