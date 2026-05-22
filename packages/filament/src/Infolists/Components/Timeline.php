<?php

namespace Lunar\Filament\Infolists\Components;

use Filament\Infolists\Components\Entry;

class Timeline extends Entry
{
    protected string $view = 'lunar-filament::infolists.components.timeline';

    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpanFull();
    }
}
