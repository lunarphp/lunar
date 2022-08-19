<?php

namespace GetCandy\Base;

use GetCandy\Models\CartLine;

interface DiscountTypeInterface
{
    /**
     * Return the name of the discount type.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Execute and apply the discount if conditions are met.
     *
     * @param CartLine $cartLine
     *
     * @return CartLine
     */
    public function execute(CartLine $cartLine): CartLine;
}
