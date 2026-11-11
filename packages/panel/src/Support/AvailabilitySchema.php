<?php

namespace Lunar\Panel\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\CustomerGroup;

/**
 * Serializes a model's channel and customer-group availability pivots into
 * draftable per-row fields (`channel:{id}`, `customer_group:{id}`), so two
 * staff scheduling different rows never conflict. Each value is a small
 * canonical map — `enabled`, `starts_at`, `ends_at`, plus `visible` on
 * customer-group rows — normalised so equality against the stored pivot
 * holds. Built for collections first; products adopt the same shape later.
 */
class AvailabilitySchema
{
    public const CHANNEL_PREFIX = 'channel:';

    public const CUSTOMER_GROUP_PREFIX = 'customer_group:';

    /**
     * Whether customer-group rows carry the product pivot's extra
     * `purchasable` flag. Off for collections, whose pivot has no such
     * column.
     */
    protected bool $withPurchasable = false;

    public function withPurchasable(): static
    {
        $schema = clone $this;
        $schema->withPurchasable = true;

        return $schema;
    }

    /**
     * Every draftable availability field key, or one side's when a prefix
     * is given.
     *
     * @return array<int, string>
     */
    public function fields(?string $side = null): array
    {
        return [
            ...($this->includes($side, static::CHANNEL_PREFIX)
                ? Channel::query()->pluck('id')->map(fn (int $id) => static::CHANNEL_PREFIX.$id)
                : []),
            ...($this->includes($side, static::CUSTOMER_GROUP_PREFIX)
                ? CustomerGroup::query()->pluck('id')->map(fn (int $id) => static::CUSTOMER_GROUP_PREFIX.$id)
                : []),
        ];
    }

    /**
     * Current pivot state per draft field key, or one side's when a prefix
     * is given.
     *
     * @return array<string, array<string, mixed>>
     */
    public function values(Model $model, ?string $side = null): array
    {
        $values = [];

        if ($this->includes($side, static::CHANNEL_PREFIX)) {
            $channelPivots = $model->channels()->get()->keyBy('id');

            foreach (Channel::query()->get(['id']) as $channel) {
                $pivot = $channelPivots->get($channel->id)?->pivot;

                $values[static::CHANNEL_PREFIX.$channel->id] = $this->normalizeValue([
                    'enabled' => (bool) ($pivot->enabled ?? false),
                    'starts_at' => $pivot->starts_at ?? null,
                    'ends_at' => $pivot->ends_at ?? null,
                ]);
            }
        }

        if ($this->includes($side, static::CUSTOMER_GROUP_PREFIX)) {
            $groupPivots = $model->customerGroups()->get()->keyBy('id');

            foreach (CustomerGroup::query()->get(['id']) as $group) {
                $pivot = $groupPivots->get($group->id)?->pivot;

                $value = [
                    'enabled' => (bool) ($pivot->enabled ?? false),
                    'visible' => (bool) ($pivot->visible ?? true),
                    'starts_at' => $pivot->starts_at ?? null,
                    'ends_at' => $pivot->ends_at ?? null,
                ];

                if ($this->withPurchasable) {
                    $value['purchasable'] = (bool) ($pivot->purchasable ?? true);
                }

                $values[static::CUSTOMER_GROUP_PREFIX.$group->id] = $this->normalizeValue($value);
            }
        }

        return $values;
    }

    /**
     * Whether a side selector (null for both) covers the given prefix.
     */
    protected function includes(?string $side, string $prefix): bool
    {
        return $side === null || $side === $prefix;
    }

    /**
     * The rows the availability card renders: id, name and the draft key.
     *
     * @return array{channels: array<int, array<string, mixed>>, customer_groups: array<int, array<string, mixed>>}
     */
    public function rows(): array
    {
        return [
            'channels' => Channel::query()->orderBy('name')->get(['id', 'name'])
                ->map(fn (Channel $channel) => [
                    'id' => $channel->id,
                    'name' => $channel->name,
                    'field' => static::CHANNEL_PREFIX.$channel->id,
                ])->all(),
            'customer_groups' => CustomerGroup::query()->orderBy('name')->get(['id', 'name'])
                ->map(fn (CustomerGroup $group) => [
                    'id' => $group->id,
                    'name' => $group->name,
                    'field' => static::CUSTOMER_GROUP_PREFIX.$group->id,
                ])->all(),
        ];
    }

    /**
     * Validation rules for every availability field, or one side's when a
     * prefix is given.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(?string $side = null): array
    {
        $rules = [];

        foreach ($this->fields($side) as $field) {
            $rules[$field] = ['nullable', 'array'];
            $rules["{$field}.enabled"] = ['boolean'];
            $rules["{$field}.starts_at"] = ['nullable', 'date'];
            $rules["{$field}.ends_at"] = ['nullable', 'date'];

            if (str_starts_with($field, static::CUSTOMER_GROUP_PREFIX)) {
                $rules["{$field}.visible"] = ['boolean'];

                if ($this->withPurchasable) {
                    $rules["{$field}.purchasable"] = ['boolean'];
                }
            }
        }

        return $rules;
    }

    /**
     * Conflict-dialog labels per field key, or one side's when a prefix is
     * given.
     *
     * @return array<string, string>
     */
    public function labels(?string $side = null): array
    {
        $labels = [];

        if ($this->includes($side, static::CHANNEL_PREFIX)) {
            foreach (Channel::query()->get(['id', 'name']) as $channel) {
                $labels[static::CHANNEL_PREFIX.$channel->id] = __('panel::availability.channels').' — '.$channel->name;
            }
        }

        if ($this->includes($side, static::CUSTOMER_GROUP_PREFIX)) {
            foreach (CustomerGroup::query()->get(['id', 'name']) as $group) {
                $labels[static::CUSTOMER_GROUP_PREFIX.$group->id] = __('panel::availability.customer_groups').' — '.$group->name;
            }
        }

        return $labels;
    }

    /**
     * Turn one side's full value set (keyed by bare row id, every row
     * present) into the pivot map the update actions sync: id => canonical
     * row, with keys the side's pivot lacks dropped.
     *
     * @param  array<int|string, mixed>  $values
     * @return array<int, array<string, mixed>>
     */
    public function pivotRows(string $side, array $values): array
    {
        $rows = [];

        foreach ($values as $id => $value) {
            $value = $this->normalizeValue((array) $value);

            if ($side === static::CHANNEL_PREFIX) {
                unset($value['visible'], $value['purchasable']);
            } elseif (! $this->withPurchasable) {
                unset($value['purchasable']);
            }

            $rows[(int) $id] = $value;
        }

        return $rows;
    }

    /**
     * Canonicalise an availability value so draft equality holds: keys are
     * sorted, booleans are real booleans and dates share one format.
     *
     * @param  array<string, mixed>  $value
     * @return array<string, mixed>
     */
    public function normalizeValue(array $value): array
    {
        $normalized = [
            'enabled' => (bool) ($value['enabled'] ?? false),
            'starts_at' => $this->normalizeDate($value['starts_at'] ?? null),
            'ends_at' => $this->normalizeDate($value['ends_at'] ?? null),
        ];

        if (array_key_exists('visible', $value)) {
            $normalized['visible'] = (bool) $value['visible'];
        }

        if ($this->withPurchasable && array_key_exists('purchasable', $value)) {
            $normalized['purchasable'] = (bool) $value['purchasable'];
        }

        ksort($normalized);

        return $normalized;
    }

    protected function normalizeDate(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        // Normalisation runs before validation; an unparsable value passes
        // through untouched so the date rule rejects it with a message
        // rather than a parse exception.
        try {
            return Carbon::parse($value)->toDateTimeString();
        } catch (\Throwable) {
            return is_string($value) ? $value : null;
        }
    }

    /**
     * Split committed draft values into UpdatesCollection's pivot maps. When
     * any row of a side was drafted, the whole side is rebuilt from the
     * stored pivots overlaid with the drafted rows — the sync replaces the
     * full row set, so untouched rows must ride along.
     *
     * @param  array<string, mixed>  $values
     * @return array{attributes: array<string, mixed>, channels: ?array<int, array<string, mixed>>, customerGroups: ?array<int, array<string, mixed>>}
     */
    public function extract(Model $model, array $values): array
    {
        $drafted = collect($values)->filter(
            fn (mixed $value, string $key) => str_starts_with($key, static::CHANNEL_PREFIX)
                || str_starts_with($key, static::CUSTOMER_GROUP_PREFIX)
        );

        $attributes = collect($values)->except($drafted->keys())->all();

        if ($drafted->isEmpty()) {
            return ['attributes' => $attributes, 'channels' => null, 'customerGroups' => null];
        }

        $current = collect($this->values($model))->merge($drafted);

        $channels = [];
        $customerGroups = [];

        foreach ($current as $key => $value) {
            $value = $this->normalizeValue((array) $value);

            if (str_starts_with($key, static::CHANNEL_PREFIX)) {
                // The channelables pivot has no visible or purchasable column;
                // drop stray keys rather than letting them reach the sync.
                unset($value['visible'], $value['purchasable']);

                $channels[(int) substr($key, strlen(static::CHANNEL_PREFIX))] = $value;
            } else {
                if (! $this->withPurchasable) {
                    unset($value['purchasable']);
                }

                $customerGroups[(int) substr($key, strlen(static::CUSTOMER_GROUP_PREFIX))] = $value;
            }
        }

        $hasChannelDrafts = $drafted->keys()->contains(fn (string $key) => str_starts_with($key, static::CHANNEL_PREFIX));
        $hasGroupDrafts = $drafted->keys()->contains(fn (string $key) => str_starts_with($key, static::CUSTOMER_GROUP_PREFIX));

        return [
            'attributes' => $attributes,
            'channels' => $hasChannelDrafts ? $channels : null,
            'customerGroups' => $hasGroupDrafts ? $customerGroups : null,
        ];
    }
}
