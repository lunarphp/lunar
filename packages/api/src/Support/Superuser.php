<?php

namespace Lunar\Api\Support;

use Illuminate\Contracts\Auth\Access\Authorizable;

/**
 * A principal that can do everything, for console output that should show the
 * whole schema rather than the anonymous view.
 */
final class Superuser implements Authorizable
{
    public function can($abilities, $arguments = []): bool
    {
        return true;
    }
}
