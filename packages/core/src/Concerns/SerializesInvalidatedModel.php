<?php

namespace Lunar\Core\Concerns;

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
            if (is_array($value) && array_key_exists('__lunarModel', $value)) {
                $value = (new $value['__lunarModel'])->newFromBuilder($value['__lunarAttributes']);
            }

            $this->{$property} = $value;
        }
    }
}
