<?php

namespace Lunar\Api\Contracts;

use Lunar\Api\OpenApi\Schema;

/**
 * A request or response body that is not a plain resource, described by hand
 * for the OpenAPI document. On a form request it replaces the rule-derived
 * body schema; on a `Responds` attribute it is the success body.
 */
interface DescribesSchema
{
    public static function schema(): Schema;
}
