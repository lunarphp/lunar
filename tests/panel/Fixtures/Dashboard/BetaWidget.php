<?php

namespace Lunar\Tests\Panel\Fixtures\Dashboard;

use Lunar\Panel\Support\Position;

class BetaWidget extends FixtureWidget
{
    public function key(): string
    {
        return 'beta';
    }

    public function position(): Position
    {
        return Position::priority(10);
    }
}
