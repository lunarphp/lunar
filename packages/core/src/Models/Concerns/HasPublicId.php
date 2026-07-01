<?php

namespace Lunar\Core\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Lunar\Core\Models\Builders\Builder;

/**
 * Gives a model a stable, non-sequential external identifier (`public_id`,
 * a ULID) alongside its integer primary key. The key stays internal; the
 * `public_id` is the outward-facing handle for APIs, webhooks, and syncs.
 */
trait HasPublicId
{
    /**
     * Mint the id as the model is constructed, so it is present even when the
     * row is persisted through a path that suppresses model events —
     * `createQuietly()`, `saveQuietly()`, `withoutEvents()` — which never reach
     * the `creating` hook below. A value supplied by mass assignment or direct
     * set overrides this, since both run after the constructor. Runs on
     * hydration too, where the stored value immediately replaces it.
     */
    public function initializeHasPublicId(): void
    {
        if (empty($this->public_id)) {
            $this->public_id = (string) Str::ulid();
        }
    }

    public static function bootHasPublicId(): void
    {
        static::creating(function (Model $model) {
            // Non-clobbering: a value supplied explicitly (e.g. a sync carrying
            // an upstream id across environments) is preserved; otherwise mint
            // one. Also re-mints when a null is assigned after construction.
            if (empty($model->public_id)) {
                $model->public_id = (string) Str::ulid();
            }
        });

        // A replica is a distinct record; clear the copied id so a fresh one is
        // minted on save rather than colliding with the source's unique value.
        static::replicating(function (Model $model) {
            $model->public_id = null;
        });
    }

    /**
     * @param  Builder<static>  $query
     * @param  string|array<int, string>  $publicId
     * @return Builder<static>
     */
    public function scopeWherePublicId(Builder $query, string|array $publicId): Builder
    {
        return $query->whereIn('public_id', (array) $publicId);
    }
}
