<?php

namespace Lunar\Panel\Sections\Sales;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Lunar\Core\Contracts\Actions\Discounts\UpdatesDiscount;
use Lunar\Core\Models\Discount;
use Lunar\Panel\Drafts\DraftableResource;
use Lunar\Panel\Http\Requests\Discounts\DiscountRequest;
use Lunar\Panel\Support\AvailabilitySchema;
use Lunar\Panel\Support\DiscountDataSchema;
use Lunar\Panel\Support\DiscountTargetSchema;

class DiscountDraftResource extends DraftableResource
{
    /** Text columns bound to inputs that submit an empty string for "unset". */
    private const NULLABLE_TEXT_FIELDS = ['coupon'];

    /** Numeric columns that are nullable but bound to inputs submitting ''. */
    private const NULLABLE_INT_FIELDS = ['max_uses', 'max_uses_per_user'];

    public function __construct(
        protected UpdatesDiscount $updatesDiscount,
        protected AvailabilitySchema $availabilitySchema,
        protected DiscountDataSchema $dataSchema,
        protected DiscountTargetSchema $targetSchema,
    ) {}

    public function model(): string
    {
        return Discount::class;
    }

    public function fields(): array
    {
        return [
            'name',
            'handle',
            'coupon',
            'starts_at',
            'ends_at',
            'priority',
            'stop',
            'max_uses',
            'max_uses_per_user',
            // The type owns the shape of `data`, so it drafts and conflict-checks
            // as one unit rather than field by field.
            'data',
            ...$this->availabilitySchema->fields(),
            ...$this->targetSchema->fields(),
        ];
    }

    public function currentValues(Model $record): array
    {
        /** @var Discount $record */
        return [
            'name' => $record->name,
            'handle' => $record->handle,
            'coupon' => $record->coupon,
            'starts_at' => $this->normalizeDate($record->starts_at),
            'ends_at' => $this->normalizeDate($record->ends_at),
            'priority' => (int) $record->priority,
            'stop' => (bool) $record->stop,
            'max_uses' => $record->max_uses === null ? null : (int) $record->max_uses,
            'max_uses_per_user' => $record->max_uses_per_user === null ? null : (int) $record->max_uses_per_user,
            'data' => $this->dataSchema->toForm($record->type, $record->data ?? []),
            ...$this->availabilitySchema->values($record),
            ...$this->targetSchema->values($record),
        ];
    }

    public function normalize(array $data): array
    {
        foreach (self::NULLABLE_TEXT_FIELDS as $field) {
            if (array_key_exists($field, $data) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        foreach (self::NULLABLE_INT_FIELDS as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = blank($data[$field]) ? null : (int) $data[$field];
            }
        }

        // The cast upper-cases on write, so the draft has to as well or every
        // lower-case keystroke reads as a change against the stored value.
        if (array_key_exists('coupon', $data) && is_string($data['coupon'])) {
            $data['coupon'] = Str::upper($data['coupon']);
        }

        foreach (['starts_at', 'ends_at'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $this->normalizeDate($data[$field]);
            }
        }

        if (array_key_exists('priority', $data)) {
            $data['priority'] = blank($data['priority']) ? null : (int) $data['priority'];
        }

        if (array_key_exists('stop', $data)) {
            $data['stop'] = (bool) $data['stop'];
        }

        foreach ($data as $key => $value) {
            if (str_starts_with($key, AvailabilitySchema::CHANNEL_PREFIX)
                || str_starts_with($key, AvailabilitySchema::CUSTOMER_GROUP_PREFIX)) {
                $data[$key] = $this->availabilitySchema->normalizeValue((array) $value);
            }

            if (str_starts_with($key, DiscountTargetSchema::PREFIX)) {
                $data[$key] = $this->targetSchema->normalizeValue($key, (array) $value);
            }
        }

        return $data;
    }

    public function rules(Model $record): array
    {
        /** @var Discount $record */
        $rules = [
            ...DiscountRequest::rulesFor($record, $record->type),
            ...$this->availabilitySchema->rules(),
            ...$this->targetSchema->rules(),
        ];

        // The endpoint validates a whole discount; a commit only ever carries
        // draftable fields, so rules for the rest — `type` above all, which is
        // fixed once the discount exists — would reject every commit.
        $draftable = array_flip($this->fields());

        return array_filter(
            $rules,
            fn (string $key) => isset($draftable[strtok($key, '.')]),
            ARRAY_FILTER_USE_KEY,
        );
    }

    public function commit(Model $record, array $values): void
    {
        /** @var Discount $record */
        $targets = $this->targetSchema->extract($values);

        $availability = $this->availabilitySchema->extract($record, $targets['attributes']);

        $attributes = $availability['attributes'];

        if (array_key_exists('data', $attributes)) {
            $attributes['data'] = $this->dataSchema->toStorage(
                $record->type,
                (array) $attributes['data'],
            );
        }

        $this->updatesDiscount->execute(
            $record,
            $attributes,
            $availability['channels'],
            $availability['customerGroups'],
            $targets['targets'],
        );
    }

    public function labels(): array
    {
        return [
            'name' => 'panel::discounts.field_name',
            'handle' => 'panel::discounts.field_handle',
            'coupon' => 'panel::discounts.field_coupon',
            'starts_at' => 'panel::discounts.field_starts_at',
            'ends_at' => 'panel::discounts.field_ends_at',
            'priority' => 'panel::discounts.field_priority',
            'stop' => 'panel::discounts.field_stop',
            'max_uses' => 'panel::discounts.field_max_uses',
            'max_uses_per_user' => 'panel::discounts.field_max_uses_per_user',
            'data' => 'panel::discounts.section_configuration',
            ...$this->availabilitySchema->labels(),
            ...$this->targetSchema->labels(),
        ];
    }

    /**
     * One date format on both sides of the comparison, so a draft that only
     * re-submits the value it was given does not read as a change.
     */
    protected function normalizeDate(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateTimeString();
        } catch (\Throwable) {
            return is_string($value) ? $value : null;
        }
    }
}
