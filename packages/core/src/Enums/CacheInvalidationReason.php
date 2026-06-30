<?php

namespace Lunar\Core\Enums;

/**
 * Why a cacheable entity is being invalidated. Informational for listeners and
 * future webhook payloads; it does not change which tag invalidates. `severity`
 * resolves dedup when one entity is recorded more than once in a flush window.
 */
enum CacheInvalidationReason: string
{
    /** The entity itself was created. */
    case Created = 'created';

    /** The entity itself was updated. */
    case Updated = 'updated';

    /** The entity itself was deleted. */
    case Deleted = 'deleted';

    /** A related entity changed (a variant, price, association, membership, ...). */
    case RelatedChanged = 'related_changed';

    /** Higher wins when the same entity is recorded under more than one reason. */
    public function severity(): int
    {
        return match ($this) {
            self::Deleted => 3,
            self::Updated => 2,
            self::Created => 1,
            self::RelatedChanged => 0,
        };
    }
}
