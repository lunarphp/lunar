<?php

namespace Lunar\Panel\Support;

use Illuminate\Support\Facades\Log;

/**
 * Orders any list whose items expose a key and a {@see Position}. Non-anchored
 * entries sort by weight (priority, with first/last pinned to the ends); then
 * `before`/`after` entries are inserted adjacent to their target key. An anchor
 * whose target is absent (typo, cross-section reference, cycle) falls back to
 * its own priority position and is logged, never dropped.
 */
class OrderResolver
{
    /**
     * @template T
     *
     * @param  iterable<T>  $items
     * @param  callable(T): string  $keyOf
     * @param  callable(T): Position  $positionOf
     * @return list<T>
     */
    public function sort(iterable $items, callable $keyOf, callable $positionOf): array
    {
        $entries = [];
        $registration = 0;

        foreach ($items as $item) {
            $position = $positionOf($item);

            $entries[] = [
                'item' => $item,
                'key' => $keyOf($item),
                'position' => $position,
                'weight' => $position->weight(),
                'registration' => $registration++,
            ];
        }

        $anchored = array_values(array_filter($entries, fn (array $e) => $e['position']->isAnchored()));
        $working = array_values(array_filter($entries, fn (array $e) => ! $e['position']->isAnchored()));

        usort($working, $this->byWeightThenRegistration(...));

        // Process anchors low-priority first so same-target siblings read left
        // to right in ascending priority.
        usort($anchored, $this->byWeightThenRegistration(...));

        $working = $this->insertAnchored($working, $anchored);

        return array_map(fn (array $e) => $e['item'], $working);
    }

    /**
     * @param  list<array<string, mixed>>  $working
     * @param  list<array<string, mixed>>  $anchored
     * @return list<array<string, mixed>>
     */
    protected function insertAnchored(array $working, array $anchored): array
    {
        $pending = $anchored;

        // Repeatedly place any anchor whose target is present, until a full
        // pass places nothing new (unresolved targets or cycles remain).
        do {
            $progress = false;

            foreach ($pending as $index => $entry) {
                $targetIndex = $this->indexOfKey($working, $entry['position']->reference);

                if ($targetIndex === null) {
                    continue;
                }

                $working = $this->insertRelative($working, $entry, $targetIndex);
                unset($pending[$index]);
                $progress = true;
            }

            $pending = array_values($pending);
        } while ($progress && $pending !== []);

        foreach ($pending as $entry) {
            Log::warning("Panel order anchor could not be resolved: '{$entry['key']}' targets missing key '{$entry['position']->reference}'.");
            $working = $this->insertByWeight($working, $entry);
        }

        return $working;
    }

    /**
     * @param  list<array<string, mixed>>  $working
     * @param  array<string, mixed>  $entry
     * @return list<array<string, mixed>>
     */
    protected function insertRelative(array $working, array $entry, int $targetIndex): array
    {
        if ($entry['position']->type === 'before') {
            $at = $targetIndex;
        } else {
            // after: skip past siblings already anchored after the same target
            // so ascending-priority siblings stay in order.
            $at = $targetIndex + 1;

            while (
                isset($working[$at])
                && $working[$at]['position']->isAnchored()
                && $working[$at]['position']->type === 'after'
                && $working[$at]['position']->reference === $entry['position']->reference
            ) {
                $at++;
            }
        }

        array_splice($working, $at, 0, [$entry]);

        return $working;
    }

    /**
     * @param  list<array<string, mixed>>  $working
     * @param  array<string, mixed>  $entry
     * @return list<array<string, mixed>>
     */
    protected function insertByWeight(array $working, array $entry): array
    {
        foreach ($working as $index => $existing) {
            if ($existing['weight'] > $entry['weight']) {
                array_splice($working, $index, 0, [$entry]);

                return $working;
            }
        }

        $working[] = $entry;

        return $working;
    }

    /** @param list<array<string, mixed>> $working */
    protected function indexOfKey(array $working, string|int|null $key): ?int
    {
        foreach ($working as $index => $entry) {
            if ($entry['key'] === $key) {
                return $index;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $a
     * @param  array<string, mixed>  $b
     */
    protected function byWeightThenRegistration(array $a, array $b): int
    {
        return [$a['weight'], $a['registration']] <=> [$b['weight'], $b['registration']];
    }
}
