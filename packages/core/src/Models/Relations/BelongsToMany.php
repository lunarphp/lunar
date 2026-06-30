<?php

namespace Lunar\Core\Models\Relations;

use Illuminate\Database\Eloquent\Relations\BelongsToMany as BaseBelongsToMany;

/**
 * A `BelongsToMany` that records cache invalidation on native pivot mutations.
 *
 * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends BaseBelongsToMany<TRelatedModel, TDeclaringModel>
 */
class BelongsToMany extends BaseBelongsToMany
{
    use RecordsCacheInvalidation;
}
