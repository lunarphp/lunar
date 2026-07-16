<?php

namespace Lunar\Core\Cache;

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Contracts\CacheInvalidator as CacheInvalidatorContract;
use Lunar\Core\Enums\CacheInvalidationReason;

/**
 * Request-scoped recorder. Collects invalidation targets deduped by entity,
 * keeping the strongest reason, and flushes one event per entity after the
 * surrounding transaction commits (or immediately when none is open).
 *
 * Reliability bias: a false positive (an extra invalidation) costs a recompute,
 * a false negative serves stale data — so the buffer errs toward firing. A full
 * rollback discards the after-commit callback and the entry never flushes within
 * the request; a nested/savepoint rollback whose outer commits may over-invalidate,
 * which is accepted.
 */
class CacheInvalidator implements CacheInvalidatorContract
{
    /** @var array<string, array{model: Model, reason: CacheInvalidationReason}> */
    protected array $pending = [];

    public function __construct(
        protected DatabaseManager $db,
        protected ?string $connection = null,
    ) {}

    public function record(Model $model, CacheInvalidationReason $reason): void
    {
        $connection = $this->db->connection($this->connection);
        $inTransaction = $connection->transactionLevel() > 0;

        foreach ($model->cacheInvalidationTargets() as $target) {
            if (! $target instanceof Model || ! method_exists($target, 'newCacheInvalidationEvent')) {
                continue;
            }

            $key = "{$target->getMorphClass()}:{$target->getKey()}";
            $isNew = ! isset($this->pending[$key]);

            if ($isNew || $reason->severity() > $this->pending[$key]['reason']->severity()) {
                $this->pending[$key] = ['model' => $target, 'reason' => $reason];
            }

            // One after-commit flush per distinct entity is enough; re-records of
            // the same entity dedup into the existing callback's batch.
            if ($isNew && $inTransaction) {
                $connection->afterCommit(fn () => $this->flush());
            }
        }

        if (! $inTransaction) {
            $this->flush();
        }
    }

    public function flush(): void
    {
        if ($this->pending === []) {
            return;
        }

        $batch = $this->pending;
        $this->pending = [];

        foreach ($batch as $entry) {
            event($entry['model']->newCacheInvalidationEvent($entry['reason']));
        }
    }
}
