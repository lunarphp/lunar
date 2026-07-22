<?php

namespace Lunar\Tests\Panel\Fixtures\Dashboard;

use Lunar\Panel\Support\Position;

class AlphaWidget extends FixtureWidget
{
    public function key(): string
    {
        return 'alpha';
    }

    public function position(): Position
    {
        return Position::priority(20);
    }
}
