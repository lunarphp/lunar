<?php

namespace Lunar\Core\Models\Relations;

use Illuminate\Database\Eloquent\Relations\MorphToMany as BaseMorphToMany;

/**
 * A `MorphToMany` that records cache invalidation on native pivot mutations
 * (the polymorphic counterpart to {@see BelongsToMany}; covers `HasChannels`).
 *
 * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends BaseMorphToMany<TRelatedModel, TDeclaringModel>
 */
class MorphToMany extends BaseMorphToMany
{
    use RecordsCacheInvalidation;
}
