<?php

namespace Lunar\Bundles\Exceptions;

use Exception;

/**
 * A component or group sync would leave the bundle breaking one of its
 * structural invariants (see spec 0085, section 2.2).
 */
class InvalidBundleDefinition extends Exception
{
    public static function empty(): self
    {
        return new self(__('bundles::bundles.definition.empty'));
    }

    public static function tooManyComponents(int $max): self
    {
        return new self(__('bundles::bundles.definition.too_many_components', ['max' => $max]));
    }

    public static function unknownGroup(): self
    {
        return new self(__('bundles::bundles.definition.unknown_group'));
    }

    public static function groupSelections(string $group): self
    {
        return new self(__('bundles::bundles.definition.group_selections', ['group' => $group]));
    }
}
