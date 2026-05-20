<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Rector;

/**
 * Catalog of Rector rule sets contributed by v2 breaking specs.
 *
 * Each set is a list of fully-qualified Rector rule classes. Subsequent
 * specs append their rules here when landing.
 */
final class LunarSetList
{
    /**
     * Rules that take v1.x consumer code to v2.x.
     *
     * @var array<int, class-string>
     */
    public const V1_TO_V2 = [
    ];
}
