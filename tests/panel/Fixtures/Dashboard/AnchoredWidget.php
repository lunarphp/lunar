<?php

namespace Lunar\Tests\Panel\Fixtures\Dashboard;

use Lunar\Panel\Support\Position;

class AnchoredWidget extends FixtureWidget
{
    public function key(): string
    {
        return 'anchored';
    }

    public function position(): Position
    {
        return Position::after('beta');
    }
}
