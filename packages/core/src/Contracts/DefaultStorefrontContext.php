<?php

namespace Lunar\Core\Contracts;

use Closure;
use Lunar\Core\DataObjects\StorefrontContext;

/**
 * The storefront context used when an operation is not given one. Scoped:
 * a registered resolver lasts for the current request or job only.
 */
interface DefaultStorefrontContext
{
    /**
     * @param  Closure(): StorefrontContext  $resolver
     */
    public function resolveUsing(Closure $resolver): void;

    public function hasResolver(): bool;

    /**
     * The registered resolver's context, or store defaults when none is registered.
     */
    public function get(): StorefrontContext;
}
