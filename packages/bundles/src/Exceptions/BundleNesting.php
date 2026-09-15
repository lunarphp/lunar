<?php

namespace Lunar\Bundles\Exceptions;

use Exception;

/**
 * A bundle would contain a bundle, or itself. Bundles are one level deep.
 */
class BundleNesting extends Exception
{
    public static function isComponent(): self
    {
        return new self(__('bundles::bundles.nesting.is_component'));
    }

    public static function isBundle(): self
    {
        return new self(__('bundles::bundles.nesting.is_bundle'));
    }

    public static function self(): self
    {
        return new self(__('bundles::bundles.nesting.self'));
    }
}
