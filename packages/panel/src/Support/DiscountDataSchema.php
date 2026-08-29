<?php

namespace Lunar\Panel\Support;

use Lunar\Core\Models\Currency;

/**
 * Composes a discount's `data` column out of its two owners: the type's own
 * payload and the conditions core reads for every type.
 *
 * Every read and write of `data` goes through here rather than through a type
 * form directly, so a form that returns only its own keys — the natural way to
 * write one — cannot drop the shared minimum-spend condition.
 */
class DiscountDataSchema
{
    public function __construct(
        protected DiscountTypeSchema $typeSchema,
        protected DiscountConditionsSchema $conditionsSchema,
    ) {}

    /**
     * @param  class-string  $discountType
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function toForm(string $discountType, array $data): array
    {
        return [
            ...$this->typeSchema->formFor($discountType)->toForm($data),
            'min_prices' => $this->conditionsSchema->toForm($data),
        ];
    }

    /**
     * @param  class-string  $discountType
     * @param  array<string, mixed>  $form
     * @return array<string, mixed>
     */
    public function toStorage(string $discountType, array $form): array
    {
        $stored = $this->typeSchema->formFor($discountType)->toStorage($form);

        $minPrices = $this->conditionsSchema->toStorage($form);

        // Applied after the type's payload, so a form that echoes the whole
        // column back cannot write a stale minimum over the edited one.
        if ($minPrices) {
            $stored['min_prices'] = $minPrices;
        } else {
            unset($stored['min_prices']);
        }

        return $stored;
    }

    /**
     * Rules for the whole `data` payload, keyed without the `data.` prefix.
     *
     * @param  class-string  $discountType
     * @return array<string, mixed>
     */
    public function rules(string $discountType): array
    {
        return [
            ...$this->typeSchema->formFor($discountType)->rules(),
            ...$this->conditionsSchema->rules(),
        ];
    }

    /**
     * @param  class-string  $discountType
     * @param  array<string, mixed>  $data  the stored payload
     */
    public function summary(string $discountType, array $data, ?Currency $currency): ?string
    {
        return $this->typeSchema->formFor($discountType)->summary($data, $currency);
    }
}
