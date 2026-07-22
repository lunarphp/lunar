<?php

namespace Lunar\Tests\Panel\Fixtures\Dashboard;

class GatedWidget extends FixtureWidget
{
    public function key(): string
    {
        return 'gated';
    }

    public function permission(): ?string
    {
        return 'sales:manage-orders';
    }

    public function visibleByDefault(): bool
    {
        return false;
    }
}
