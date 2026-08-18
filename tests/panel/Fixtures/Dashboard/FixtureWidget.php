<?php

namespace Lunar\Tests\Panel\Fixtures\Dashboard;

use Lunar\Panel\Dashboard\DashboardRange;
use Lunar\Panel\Dashboard\Widget;

abstract class FixtureWidget extends Widget
{
    public function component(): string
    {
        return 'FixtureWidget';
    }

    public function label(): string
    {
        return ucfirst($this->key());
    }

    public function data(DashboardRange $range): array
    {
        return ['key' => $this->key()];
    }
}
