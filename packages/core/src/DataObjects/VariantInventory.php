<?php

namespace Lunar\Core\DataObjects;

/**
 * A variant's sellable inventory as answered by the `ResolvesInventory` seam.
 *
 * `available` is the number of units that can be sold now; `unlimited` means
 * selling continues regardless of that number (the "always" selling policy).
 */
final readonly class VariantInventory
{
    public function __construct(
        public int $available,
        public bool $unlimited = false,
    ) {}

    public function allows(int $quantity): bool
    {
        return $this->unlimited || $quantity <= $this->available;
    }
}
