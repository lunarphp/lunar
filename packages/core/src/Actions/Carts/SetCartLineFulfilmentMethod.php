<?php

namespace Lunar\Core\Actions\Carts;

use Lunar\Core\Contracts\Actions\Carts\GetsExistingCartLine;
use Lunar\Core\Contracts\Actions\Carts\SetsCartLineFulfilmentMethod;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\CartLine;

class SetCartLineFulfilmentMethod implements SetsCartLineFulfilmentMethod
{
    public function __construct(
        protected GetsExistingCartLine $getsExistingCartLine,
    ) {}

    /**
     * The method is part of a line's identity, so a line changed to match
     * another of the shopper's lines merges into it. Returns the line that
     * holds the quantity afterwards.
     */
    public function execute(CartLine $cartLine, ?string $fulfilmentMethod): CartLine
    {
        /** @var CartLine $cartLine */
        $cartLine->loadMissing(['cart', 'purchasable']);

        return DB::transaction(function () use ($cartLine, $fulfilmentMethod) {
            $existing = $cartLine->isAddedByDiscount() ? null : $this->getsExistingCartLine->execute(
                cart: $cartLine->cart,
                purchasable: $cartLine->purchasable,
                meta: $cartLine->meta?->getArrayCopy() ?? [],
                fulfilmentMethod: $fulfilmentMethod,
            );

            if ($existing && $existing->isNot($cartLine)) {
                $existing->update(['quantity' => $existing->quantity + $cartLine->quantity]);
                $cartLine->delete();

                return $existing;
            }

            $cartLine->update(['fulfilment_method' => $fulfilmentMethod]);

            return $cartLine;
        });
    }
}
