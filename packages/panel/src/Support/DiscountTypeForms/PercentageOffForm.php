<?php

namespace Lunar\Panel\Support\DiscountTypeForms;

use Lunar\Core\Models\Currency;
use Lunar\Panel\Contracts\DiscountTypeForm;

/**
 * PercentageOff stores a single `data.percentage`, applied to each eligible
 * line — no money scaling, so the form value is the stored value.
 */
class PercentageOffForm implements DiscountTypeForm
{
    public function component(): string
    {
        return 'PercentageOffForm';
    }

    public function targetBuckets(): array
    {
        return ['limitation', 'exclusion'];
    }

    public function toForm(array $data): array
    {
        return ['percentage' => (float) ($data['percentage'] ?? 0)];
    }

    public function toStorage(array $data): array
    {
        return ['percentage' => (float) ($data['percentage'] ?? 0)];
    }

    public function rules(): array
    {
        return ['percentage' => ['required', 'numeric', 'min:0', 'max:100']];
    }

    public function summary(array $data, ?Currency $currency): ?string
    {
        $percentage = (float) ($data['percentage'] ?? 0);

        if (! $percentage) {
            return null;
        }

        return __('panel::discounts.summary_percentage_off', [
            'percentage' => rtrim(rtrim(number_format($percentage, 2, '.', ''), '0'), '.'),
        ]);
    }
}
