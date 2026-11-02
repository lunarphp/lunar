<?php

namespace Lunar\Bundles\Enums;

/**
 * How a bundle variant is priced.
 */
enum BundlePricing: string
{
    /** The merchant prices the bundle variant by hand, like any other variant. */
    case Fixed = 'fixed';

    /** The price is the quantity-weighted sum of the components, less the discount percentage. */
    case Components = 'components';
}
