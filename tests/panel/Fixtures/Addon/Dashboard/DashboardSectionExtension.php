<?php

namespace Lunar\Tests\Panel\Fixtures\Addon\Dashboard;

use Lunar\Panel\Sections\SectionExtension;

class DashboardSectionExtension extends SectionExtension
{
    public function extends(): string
    {
        return 'dashboard';
    }

    public function widgets(): array
    {
        return [AddonSalesWidget::class];
    }
}
