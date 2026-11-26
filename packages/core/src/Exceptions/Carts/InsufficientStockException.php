<?php

namespace Lunar\Core\Exceptions\Carts;

use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Support\Collection;
use Lunar\Core\Models\CartLine;

/**
 * Order creation found cart lines whose variant can no longer supply the
 * quantity in the cart. A `CartException`, so callers that already handle
 * cart failures keep working; `lines()` names the offending lines.
 */
class InsufficientStockException extends CartException
{
    /**
     * @param  Collection<int, CartLine>  $lines
     */
    public function __construct(MessageBag $messageBag, protected Collection $lines)
    {
        parent::__construct($messageBag);
    }

    /**
     * @return Collection<int, CartLine>
     */
    public function lines(): Collection
    {
        return $this->lines;
    }
}
