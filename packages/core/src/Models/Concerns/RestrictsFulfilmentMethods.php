<?php

namespace Lunar\Core\Models\Concerns;

/**
 * For a purchasable that doesn't restrict its fulfilment methods.
 */
trait RestrictsFulfilmentMethods
{
    /**
     * @return list<string>|null
     */
    public function allowedFulfilmentMethods(): ?array
    {
        return null;
    }
}
