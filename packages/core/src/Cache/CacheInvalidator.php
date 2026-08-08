<?php

namespace Lunar\Core\Cache;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\TransactionRolledBack;
use Lunar\Core\Contracts\CacheInvalidator as CacheInvalidatorContract;
use Lunar\Core\Enums\CacheInvalidationReason;

/**
 * Request-scoped recorder. Collects invalidation targets deduped by entity,
 * keeping the strongest reason, and flushes one event per entity after the
 * surrounding transaction commits (or immediately when none is open).
 *
 * Reliability bias: a false positive (an extra invalidation) costs a recompute,
 * a false negative serves stale data — so the buffer errs toward firing. Each
 * entry remembers the shallowest transaction level it was touched at; a
 * rollback (full or to a savepoint) discards only entries whose shallowest
 * touch was inside the frame being discarded. An entry also touched from a
 * surviving outer frame is kept, which is accepted as an over-invalidation.
 */
class CacheInvalidator implements CacheInvalidatorContract
{
    /** @var array<string, array{model: Model, reason: CacheInvalidationReason, level: int}> */
    protected array $pending = [];

    /** @var array<string, true> */
    protected array $rollbackListeners = [];

    public function __construct(
        protected DatabaseManager $db,
        protected ?string $connection = null,
    ) {}

    public function record(Model $model, CacheInvalidationReason $reason): void
    {
        $connection = $this->db->connection($this->connection);
        $level = $connection->transactionLevel();
        $inTransaction = $level > 0;

        if ($inTransaction) {
            $this->listenForRollback($connection);
        }

        foreach ($model->cacheInvalidationTargets() as $target) {
            if (! $target instanceof Model || ! method_exists($target, 'newCacheInvalidationEvent')) {
                continue;
            }

            $key = "{$target->getMorphClass()}:{$target->getKey()}";
            $isNew = ! isset($this->pending[$key]);

            if ($isNew) {
                $this->pending[$key] = ['model' => $target, 'reason' => $reason, 'level' => $level];
            } else {
                if ($reason->severity() > $this->pending[$key]['reason']->severity()) {
                    $this->pending[$key]['model'] = $target;
                    $this->pending[$key]['reason'] = $reason;
                }

                // Kept alive by the shallowest touch: if any surviving frame
                // also recorded this entity, a deeper rollback must not drop it.
                $this->pending[$key]['level'] = min($this->pending[$key]['level'], $level);
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

    protected function listenForRollback(ConnectionInterface $connection): void
    {
        $name = $connection->getName();

        if (isset($this->rollbackListeners[$name])) {
            return;
        }

        $this->rollbackListeners[$name] = true;

        $connection->getEventDispatcher()?->listen(
            TransactionRolledBack::class,
            function (TransactionRolledBack $event) use ($connection) {
                if ($event->connection !== $connection) {
                    return;
                }

                $survivingLevel = $connection->transactionLevel();

                $this->pending = array_filter(
                    $this->pending,
                    fn (array $entry) => $entry['level'] <= $survivingLevel,
                );
            },
        );
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
