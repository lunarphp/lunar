<?php

namespace Lunar\Core\Validation\Cart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;
use Lunar\Core\Exceptions\Carts\InsufficientStockException;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Validation\BaseValidator;

/**
 * Re-checks stock when the order is created (spec 0082). Lines of the same
 * purchasable share its stock, so their quantities are summed, and each
 * purchasable is read fresh rather than from the cart's cached relation.
 * A variant with `continue_selling` set always passes.
 */
class ValidateCartStock extends BaseValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate(): bool
    {
        /** @var Cart $cart */
        $cart = $this->parameters['cart'];

        $cart->loadMissing('lines.purchasable');

        $short = $cart->lines
            ->filter(fn (CartLine $line) => (bool) $line->purchasable)
            ->groupBy(fn (CartLine $line) => $line->purchasable_type.':'.$line->purchasable_id)
            ->filter(function (Collection $lines) {
                $purchasable = $lines->first()->purchasable;

                if ($purchasable instanceof Model) {
                    $purchasable = $purchasable->fresh() ?? $purchasable;
                }

                return ! $purchasable->canBeFulfilledAtQuantity((int) $lines->sum('quantity'));
            })
            ->flatten(1);

        if ($short->isEmpty()) {
            return $this->pass();
        }

        throw new InsufficientStockException(new MessageBag([
            'cart' => __('lunar::exceptions.carts.insufficient_stock', [
                'identifier' => $short
                    ->map(fn (CartLine $line) => $line->purchasable->getIdentifier() ?? "#{$line->id}")
                    ->unique()
                    ->implode(', '),
            ]),
        ]), $short->values());
    }
}
