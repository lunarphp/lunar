<?php

namespace Lunar\Panel\Support;

use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * Serialises an activity-log entry for the sidebar activity timeline shared by
 * the record edit pages (products, variants, brands, collections, ...).
 */
class TimelineActivity
{
    /**
     * @return array{description: string, created_at: ?Carbon, causer_name: ?string, avatar: ?string, changes: list<string>}
     */
    public static function toArray(Activity $activity): array
    {
        return [
            'description' => static::description($activity),
            'created_at' => $activity->created_at,
            'causer_name' => $activity->causer?->full_name ?? $activity->causer?->name ?? null,
            'avatar' => Gravatar::url($activity->causer?->email),
            'changes' => static::changedKeys($activity),
        ];
    }

    /**
     * The display label for an entry — custom panel events get a translated
     * label; plain model events keep spatie's created/updated description.
     */
    protected static function description(Activity $activity): string
    {
        if ($activity->event === 'order-address-update') {
            $type = $activity->getExtraProperty('type') === 'billing' ? 'billing' : 'shipping';

            return __('panel::orders.activity_'.$type.'_address_updated');
        }

        return (string) $activity->description;
    }

    /**
     * Changed attribute names for an update; empty for create/delete where the
     * full attribute set carries no "what changed" signal.
     *
     * Comparison is empty-aware: logOnlyDirty flags cast/state columns (status)
     * and JSON columns as dirty even when nothing meaningful changed, and empty
     * shapes serialise inconsistently ([] vs {} vs {"specs":[]}). Values that
     * normalise to the same thing are treated as unchanged. The attribute_data
     * JSON is diffed per attribute handle so the feed names the actual
     * attributes that changed rather than a vague "attribute_data".
     *
     * @return list<string>
     */
    protected static function changedKeys(Activity $activity): array
    {
        if ($activity->event === 'order-address-update') {
            $fields = (array) $activity->properties->get('fields', []);
            $new = (array) $activity->properties->get('new', []);
            $previous = (array) $activity->properties->get('previous', []);

            return array_values(array_filter(
                $fields,
                fn (string $field) => ! static::valuesEqual($previous[$field] ?? null, $new[$field] ?? null),
            ));
        }

        if ($activity->description !== 'updated') {
            return [];
        }

        $attributes = (array) $activity->properties->get('attributes', []);
        $old = (array) $activity->properties->get('old', []);

        $changes = [];

        foreach ($attributes as $key => $newValue) {
            if ($key === 'updated_at') {
                continue;
            }

            $oldValue = $old[$key] ?? null;

            if ($key === 'attribute_data') {
                $handles = static::changedHandles($oldValue, $newValue);

                if ($handles !== []) {
                    array_push($changes, ...$handles);
                } elseif (! static::valuesEqual($oldValue, $newValue)) {
                    $changes[] = $key;
                }

                continue;
            }

            if (! static::valuesEqual($oldValue, $newValue)) {
                $changes[] = $key;
            }
        }

        return array_values(array_unique($changes));
    }

    /**
     * Attribute handles whose value changed between two attribute_data payloads.
     *
     * @return list<string>
     */
    protected static function changedHandles(mixed $old, mixed $new): array
    {
        $old = is_array($old) ? $old : [];
        $new = is_array($new) ? $new : [];

        $handles = array_unique([...array_keys($old), ...array_keys($new)]);

        $changed = [];

        foreach ($handles as $handle) {
            if (! static::valuesEqual($old[$handle] ?? null, $new[$handle] ?? null)) {
                $changed[] = (string) $handle;
            }
        }

        return $changed;
    }

    /**
     * Two values are equal once empties are normalised away, so [], {},
     * {"specs":[]}, "" and null all compare as the same empty value.
     */
    protected static function valuesEqual(mixed $a, mixed $b): bool
    {
        return static::normalize($a) == static::normalize($b);
    }

    /**
     * Recursively drop empty entries; anything empty (empty array/string/null)
     * collapses to null so all empty shapes compare equal.
     */
    protected static function normalize(mixed $value): mixed
    {
        if (is_array($value)) {
            $normalized = [];

            foreach ($value as $key => $item) {
                $item = static::normalize($item);

                if ($item !== null) {
                    $normalized[$key] = $item;
                }
            }

            return $normalized === [] ? null : $normalized;
        }

        return $value === '' ? null : $value;
    }
}
