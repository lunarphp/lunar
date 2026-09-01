<?php

namespace Lunar\Tests\Panel\Fixtures\Discounts;

use Lunar\Panel\Sections\Section;

/**
 * Registers a discount type's panel form the way a third-party package would —
 * through its own section, with no dependency on the Sales section.
 */
class DiscountFixtureSection extends Section
{
    public function key(): string
    {
        return 'discount-fixture';
    }

    public function discountTypeForms(): array
    {
        return [FixtureDiscountType::class => FixtureDiscountTypeForm::class];
    }
}
