<?php

namespace Lunar\Core\Actions\Fulfilment;

use Illuminate\Database\Eloquent\Collection;
use Lunar\Core\Contracts\Actions\Fulfilment\MergesFulfilments;
use Lunar\Core\Exceptions\FulfilmentException;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Contracts\Fulfilment as FulfilmentContract;
use Lunar\Core\Models\Fulfilment;

/**
 * Consolidate pre-ship source fulfilments into the target. Preserves total
 * fulfilled quantity, so the rollups are untouched. The target's tracking
 * wins; the action errors rather than silently discarding conflicting
 * tracking carried by a source.
 */
final class MergeFulfilments implements MergesFulfilments
{
    /**
     * Fulfilment states that may take part in a merge.
     */
    public const MERGEABLE_STATES = ['pending', 'in-progress'];

    public function execute(FulfilmentContract $target, Collection $sources): Fulfilment
    {
        /** @var Fulfilment $target */
        if ($reason = self::ineligibilityReason($target, $sources)) {
            throw new FulfilmentException(__($reason));
        }

        return DB::transaction(function () use ($target, $sources) {
            foreach ($sources as $source) {
                /** @var Fulfilment $source */
                foreach ($source->lines as $sourceLine) {
                    $targetLine = $target->lines()
                        ->where('order_line_id', $sourceLine->order_line_id)
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

        if (! in_array($target->state::$name, self::MERGEABLE_STATES, true)) {
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

            if (! in_array($source->state::$name, self::MERGEABLE_STATES, true)) {
                return 'lunar::exceptions.fulfilment_not_mergeable';
            }

            if (self::hasConflictingTracking($target, $source)) {
                return 'lunar::exceptions.fulfilment_merge_tracking_conflict';
            }
        }

        return null;
    }

    /**
     * Whether a source carries tracking that differs from the target and
     * would be silently lost on merge.
     */
    protected static function hasConflictingTracking(Fulfilment $target, Fulfilment $source): bool
    {
        foreach (['tracking_number', 'tracking_url', 'shipping_method'] as $field) {
            if (filled($source->{$field}) && $source->{$field} !== $target->{$field}) {
                return true;
            }
        }

        return false;
    }
}
