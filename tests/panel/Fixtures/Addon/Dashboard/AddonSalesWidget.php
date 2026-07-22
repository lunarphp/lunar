<?php

namespace Lunar\Tests\Panel\Fixtures\Addon\Dashboard;

use Lunar\Panel\Dashboard\DashboardRange;
use Lunar\Panel\Dashboard\Widget;
use Lunar\Panel\Support\Position;

class AddonSalesWidget extends Widget
{
    public function key(): string
    {
        return 'addon-sales';
    }

    public function component(): string
    {
        return 'widgets::SalesWidget';
    }

    public function label(): string
    {
        return 'Add-on sales';
    }

    public function description(): ?string
    {
        return 'A widget contributed by the fixture add-on.';
    }

    public function position(): Position
    {
        return Position::after('kpis');
    }

    public function data(DashboardRange $range): array
    {
        return ['message' => "Sales for {$range->value}"];
    }
}
