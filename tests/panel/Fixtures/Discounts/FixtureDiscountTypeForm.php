<?php

namespace Lunar\Tests\Panel\Fixtures\Discounts;

use Lunar\Core\Models\Currency;
use Lunar\Panel\Contracts\DiscountTypeForm;

/**
 * The panel form for FixtureDiscountType, exercising every part of the seam:
 * its own component, a narrowed bucket list, a scaling round trip and rules.
 */
class FixtureDiscountTypeForm implements DiscountTypeForm
{
    public function component(): string
    {
        return 'fixture::DiscountForm';
    }

    public function targetBuckets(): array
    {
        return ['limitation'];
    }

    public function toForm(array $data): array
    {
        return ['tier' => ($data['tier'] ?? 0) / 100];
    }

    public function toStorage(array $data): array
    {
        return ['tier' => (int) round(((float) ($data['tier'] ?? 0)) * 100)];
    }

    public function rules(): array
    {
        return ['tier' => ['required', 'numeric', 'min:0']];
    }

    public function summary(array $data, ?Currency $currency): ?string
    {
        return 'Tier '.($data['tier'] ?? 0);
    }
}
