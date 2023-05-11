<?php

namespace Lunar\Hub\Auth;

use Illuminate\Contracts\Auth\Authenticatable;

abstract class Impersonate
{
    /**
     * Return the URL for impersonation.
     */
    abstract public function getUrl(Authenticatable $authenticatable): string;
}
