<?php

namespace Lunar\Panel\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Facades\CancelReasons;
use Lunar\Core\Facades\HoldReasons;
use Lunar\Core\Models\Currency;
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
     * Serialise an entry for the order page timeline. Every event logged
     * against an order — its own model events, cancellations, notifications,
     * transactions and fulfilment changes — gets a translated label, plus a
     * detail line (cancel note, notification message, transaction notes, hold
     * reason, comment) where the event carries one. An event the panel does
     * not know reads as a humanised label rather than its raw key.
     *
     * @return array{description: string, detail: ?string, created_at: ?Carbon, causer_name: ?string, avatar: ?string, changes: list<string>}
     */
    public static function forOrder(Activity $activity, ?Currency $currency): array
    {
        return array_merge(static::toArray($activity), [
            'description' => static::orderDescription($activity, $currency),
            'detail' => static::orderDetail($activity),
        ]);
    }

    /**
     * The translated label for an order timeline entry.
     */
    protected static function orderDescription(Activity $activity, ?Currency $currency): string
    {
        $event = (string) ($activity->event ?: $activity->description);
        $properties = $activity->properties;

        return match ($event) {
            'created', 'updated', 'deleted' => __('panel::orders.activity_'.$event),
            'order-address-update' => static::description($activity),
            'order-cancelled' => ($reason = CancelReasons::label($properties->get('reason')))
                ? __('panel::orders.activity_order_cancelled_reason', ['reason' => $reason])
                : __('panel::orders.activity_order_cancelled'),
            'order-closed' => __('panel::orders.activity_order_closed'),
            'order-reopened' => __('panel::orders.activity_order_reopened'),
            'email-notification' => __('panel::orders.activity_email_notification', [
                'notification' => (string) ($properties->get('notification') ?: __('panel::orders.notify_notification')),
                'email' => (string) $properties->get('email'),
            ]),
            'intent' => __('panel::orders.activity_payment_authorized', ['amount' => static::money($properties->get('amount'), $currency)]),
            'capture' => __('panel::orders.activity_payment_captured', ['amount' => static::money($properties->get('amount'), $currency)]),
            'refund' => __('panel::orders.activity_refund_issued', ['amount' => static::money($properties->get('amount'), $currency)]),
            'fulfilment-update' => static::fulfilmentDescription($activity),
            'comment' => __('panel::orders.activity_comment'),
            'tags-update' => __('panel::orders.activity_tags_updated'),
            default => static::humanise($event),
        };
    }

    /**
     * The secondary line for an order timeline entry, when its event carries
     * free text or a reason worth showing.
     */
    protected static function orderDetail(Activity $activity): ?string
    {
        $properties = $activity->properties;

        $detail = match ((string) $activity->event) {
            'order-cancelled' => $properties->get('note'),
            'email-notification' => $properties->get('message'),
            'intent', 'capture', 'refund' => $properties->get('notes'),
            'fulfilment-update' => $properties->get('type') === 'held'
                ? HoldReasons::label($properties->get('reason'))
                : null,
            'comment' => $properties->get('content'),
            default => null,
        };

        return is_string($detail) && trim($detail) !== '' ? $detail : null;
    }

    /**
     * The label for a fulfilment change logged against its order: a state
     * transition, a hold, or a release.
     */
    protected static function fulfilmentDescription(Activity $activity): string
    {
        $properties = $activity->properties;
        $reference = $properties->get('reference') ?: '#'.$properties->get('fulfilment_id');

        return match ($properties->get('type')) {
            'state' => __('panel::orders.activity_fulfilment_state', [
                'reference' => $reference,
                'state' => static::fulfilmentStateLabel((string) $properties->get('to')),
            ]),
            'held' => __('panel::orders.activity_fulfilment_held', ['reference' => $reference]),
            'released' => __('panel::orders.activity_fulfilment_released', ['reference' => $reference]),
            default => static::humanise((string) $activity->event),
        };
    }

    /**
     * The translated name of a fulfilment state, humanised when the state has
     * no translation (a store-registered state, for example).
     */
    protected static function fulfilmentStateLabel(string $state): string
    {
        $key = 'lunar::states.fulfilment.'.$state;

        return Lang::has($key) ? __($key) : static::humanise($state);
    }

    /**
     * A minor-unit amount formatted in the order currency, or the bare number
     * when the order has no currency to format it with.
     */
    protected static function money(mixed $amount, ?Currency $currency): string
    {
        $amount = (int) $amount;

        return $currency ? (new PriceValue($amount, $currency))->format() : (string) $amount;
    }

    /**
     * A readable label for an event key: "loyalty-points_awarded" reads as
     * "Loyalty points awarded".
     */
    protected static function humanise(string $key): string
    {
        return Str::ucfirst(str_replace(['-', '_'], ' ', $key));
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
