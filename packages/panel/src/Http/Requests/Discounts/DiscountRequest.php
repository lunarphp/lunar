<?php

namespace Lunar\Panel\Http\Requests\Discounts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Core\Contracts\DiscountType;
use Lunar\Core\Facades\Discounts;
use Lunar\Core\Models\Discount;
use Lunar\Panel\Support\DiscountTypeSchema;

/** Rules for the discount update endpoint and the drafts layer. */
class DiscountRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return static::rulesFor($this->route('discount'), $this->string('type')->value());
    }

    /**
     * The rule set, parameterised on the discount being edited so the drafts
     * layer can validate a commit payload with the same rules the update
     * endpoint applies.
     *
     * The selected type's own `data.*` rules are merged in — the type owns the
     * shape of that column, so nothing here assumes anything about it beyond
     * its being an array.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function rulesFor(?Discount $discount, ?string $type = null): array
    {
        $unique = fn (string $column) => tap(
            Rule::unique((new Discount)->getTable(), $column),
            fn ($rule) => $discount ? $rule->ignore($discount->getKey()) : null,
        );

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            // No format rule, deliberately. The Filament admin only runs the name
            // through Str::snake, which leaves punctuation in place, so stored
            // handles like `sofia_o'kon` already exist; a pattern here would make
            // them uneditable in the panel. The create screen still generates a
            // clean handle — this only governs what is accepted.
            'handle' => ['required', 'string', 'max:255', $unique('handle')],
            'coupon' => ['nullable', 'string', 'max:255', $unique('coupon')],
            'type' => ['required', 'string', Rule::in(static::registeredTypes())],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:100'],
            'stop' => ['boolean'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'max_uses_per_user' => ['nullable', 'integer', 'min:1'],
            'data' => ['nullable', 'array'],
        ];

        $type ??= $discount?->type;

        if ($type) {
            foreach (app(DiscountTypeSchema::class)->formFor($type)->rules() as $key => $rule) {
                $rules["data.{$key}"] = $rule;
            }
        }

        return $rules;
    }

    /**
     * Class names of the currently registered discount types.
     *
     * @return array<int, class-string>
     */
    public static function registeredTypes(): array
    {
        return Discounts::getTypes()
            ->map(fn (DiscountType $type) => $type::class)
            ->all();
    }
}
