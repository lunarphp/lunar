<?php

namespace Lunar\Bundles\ValueObjects;

use Lunar\Bundles\Models\BundleComponent;
use Lunar\Bundles\Models\BundleGroup;
use Lunar\Core\Models\ProductVariant;

/**
 * One resolved part of a bundle selection: the variant to supply, how many
 * per bundle unit, and the group it was chosen from (null for a fixed part).
 */
final readonly class SelectedComponent
{
    public function __construct(
        public ProductVariant $variant,
        public int $quantity,
        public ?BundleGroup $group,
        public BundleComponent $component,
    ) {}
}
