<?php

namespace Lunar\Tests\Panel\Fixtures\Dashboard;

class DuplicateAlphaWidget extends FixtureWidget
{
    public function key(): string
    {
        return 'alpha';
    }

    public function component(): string
    {
        return 'DuplicateFixtureWidget';
    }
}
