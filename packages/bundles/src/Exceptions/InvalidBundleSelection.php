<?php

namespace Lunar\Bundles\Exceptions;

use Exception;

/**
 * Cart line meta names a selection the bundle cannot satisfy.
 */
class InvalidBundleSelection extends Exception
{
    public static function unknownGroup(string $publicId): self
    {
        return new self(__('bundles::bundles.selection.unknown_group', ['id' => $publicId]));
    }

    public static function unknownComponent(string $publicId, string $group): self
    {
        return new self(__('bundles::bundles.selection.unknown_component', ['id' => $publicId, 'group' => $group]));
    }

    public static function duplicateComponent(string $group): self
    {
        return new self(__('bundles::bundles.selection.duplicate_component', ['group' => $group]));
    }

    public static function count(string $group, int $min, int $max): self
    {
        return new self(__('bundles::bundles.selection.count', ['group' => $group, 'min' => $min, 'max' => $max]));
    }

    public static function malformed(): self
    {
        return new self(__('bundles::bundles.selection.malformed'));
    }
}
