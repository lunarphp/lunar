<?php

namespace Lunar\Core\Actions\Fulfilment;

use Illuminate\Database\Eloquent\Collection;
use Lunar\Core\Contracts\Actions\Fulfilment\MergesFulfilments;
use Lunar\Core\Enums\FulfilmentStateCategory;
use Lunar\Core\Exceptions\FulfilmentException;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Contracts\Fulfilment as FulfilmentContract;
use Lunar\Core\Models\Fulfilment;

/**
 * Consolidate outstanding source fulfilments into the target. Preserves total
 * fulfilled quantity, so the rollups are untouched. The target's tracking
 * wins; the action errors rather than silently discarding conflicting
 * tracking carried by a source. Source and target must share a method — you
 * can't fold a shipping fulfilment into a collection one.
 */
class MergeFulfilments implements MergesFulfilments
{
    /**
     * Whether a fulfilment may take part in a merge — only outstanding
     * (un-handed-over) fulfilments can be merged.
     */
    public static function isMergeable(FulfilmentContract $fulfilment): bool
    {
        /** @var Fulfilment $fulfilment */
        return $fulfilment->state->category() === FulfilmentStateCategory::Outstanding;
    }

    /**
     * @param  Collection<int, FulfilmentContract>  $sources
     */
    public function execute(FulfilmentContract $target, Collection $sources): Fulfilment
    {
        /** @var Fulfilment $target */
        if ($reason = self::ineligibilityReason($target, $sources)) {
            throw new FulfilmentException(__($reason));
        }

        return DB::transaction(function () use ($target, $sources) {
            foreach ($sources as $source) {
                /** @var Fulfilment $source */
                // Locked re-read (not the relation's cached collection) so a
                // concurrent split/move of a source serialises with the merge
                // rather than the merge absorbing stale quantities.
                foreach ($source->lines()->lockForUpdate()->get() as $sourceLine) {
                    $targetLine = $target->lines()
                        ->where('order_line_id', $sourceLine->order_line_id)
                        ->lockForUpdate()
                        ->first();

                    if ($targetLine) {
                        $targetLine->update([
                            'quantity' => $targetLine->quantity + $sourceLine->quantity,
                        ]);
                    } else {
                        $target->lines()->create([
                            'order_line_id' => $sourceLine->order_line_id,
                            'quantity' => $sourceLine->quantity,
                        ]);
                    }
                }

                $source->delete();
            }

            return $target->refresh();
        });
    }

    /**
     * Whether the sources can be merged into the target — used to gate the
     * merge action in the UI (and by API consumers) without catching an
     * exception.
     *
     * @param  Collection<int, FulfilmentContract>  $sources
     */
    public static function canRun(FulfilmentContract $target, Collection $sources): bool
    {
        /** @var Fulfilment $target */
        return self::ineligibilityReason($target, $sources) === null;
    }

    /**
     * The translation key explaining why a merge cannot run, or null when it
     * is eligible. Single source of truth shared by `execute()` and
     * `canRun()`.
     *
     * @param  Collection<int, FulfilmentContract>  $sources
     */
    protected static function ineligibilityReason(Fulfilment $target, Collection $sources): ?string
    {
        if ($sources->isEmpty()) {
            return 'lunar::exceptions.fulfilment_merge_no_sources';
        }

        if (! self::isMergeable($target)) {
            return 'lunar::exceptions.fulfilment_not_mergeable';
        }

        foreach ($sources as $source) {
            /** @var Fulfilment $source */
            if ($source->getKey() === $target->getKey()) {
                return 'lunar::exceptions.fulfilment_merge_target_in_sources';
            }

            if ($source->order_id !== $target->order_id) {
                return 'lunar::exceptions.fulfilment_merge_different_orders';
            }

            if ($source->location_id !== $target->location_id) {
                return 'lunar::exceptions.fulfilment_merge_different_locations';
            }

            if ($source->method !== $target->method) {
                return 'lunar::exceptions.fulfilment_method_mismatch';
            }

            if (! self::isMergeable($source)) {
                return 'lunar::exceptions.fulfilment_not_mergeable';
            }
        }

        return null;
    }
}
