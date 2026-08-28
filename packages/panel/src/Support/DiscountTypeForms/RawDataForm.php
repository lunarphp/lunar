<?php

namespace Lunar\Panel\Support\DiscountTypeForms;

use Lunar\Core\Models\Currency;
use Lunar\Panel\Contracts\DiscountTypeForm;

/**
 * Fallback for a discount type with no registered form: a JSON editor over the
 * stored `data`, with every target bucket shown.
 *
 * A type whose package is installed but panel-unaware stays editable rather
 * than disappearing, and its data survives a round trip untouched — the panel
 * has no idea what the shape means, so it does not try to reformat it.
 */
class RawDataForm implements DiscountTypeForm
{
    public function component(): string
    {
        return 'RawDataForm';
    }

    public function targetBuckets(): array
    {
        return ['limitation', 'exclusion', 'condition', 'reward'];
    }

    public function toForm(array $data): array
    {
        return $data;
    }

    public function toStorage(array $data): array
    {
        return $data;
    }

    public function rules(): array
    {
        return [];
    }

    public function summary(array $data, ?Currency $currency): ?string
    {
        // The panel does not know what the payload means, so it says nothing
        // rather than guessing; the list falls back to the type's own name.
        return null;
    }
}
