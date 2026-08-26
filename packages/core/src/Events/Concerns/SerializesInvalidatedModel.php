<?php

namespace Lunar\Core\Events\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Queue-safe replacement for Illuminate\Queue\SerializesModels on cache
 * invalidation events. Laravel's trait restores a Model property by
 * re-querying the database with `firstOrFail()`, which throws
 * ModelNotFoundException when a Deleted invalidation's row is already gone
 * by the time a queued listener runs. This captures the model's raw
 * attributes instead and rehydrates it in memory on unserialize (no query),
 * so the scalar identity captured at construction time — cacheTags(),
 * morphType(), cacheKey() — is always readable regardless of whether the
 * row still exists.
 *
 * The restored model reflects a snapshot of its attributes taken when the
 * event was dispatched, not a fresh read — it is not re-fetched from the
 * database on unserialize. `newFromBuilder()` also fires Eloquent's
 * `retrieved` event, the same as any other model hydration.
 *
 * Loaded relations are not carried across, so reading one off the restored
 * model queries afresh (or trips a lazy-loading violation where that is
 * prevented). Capturing them is deliberately avoided: every model these
 * events wrap implements Spatie's HasMedia, whose registered media
 * collections hold closures and cannot be serialized.
 */
trait SerializesInvalidatedModel
{
    public function __serialize(): array
    {
        $data = [];

        foreach (get_object_vars($this) as $property => $value) {
            $data[$property] = $value instanceof Model
                ? ['__lunarModel' => get_class($value), '__lunarAttributes' => $value->getAttributes()]
                : $value;
        }

        return $data;
    }

    public function __unserialize(array $data): void
    {
        foreach ($data as $property => $value) {
            if (is_array($value) && array_key_exists('__lunarModel', $value) && is_a($value['__lunarModel'], Model::class, true)) {
                $value = (new $value['__lunarModel'])->newFromBuilder($value['__lunarAttributes']);
            }

            $this->{$property} = $value;
        }
    }
}
