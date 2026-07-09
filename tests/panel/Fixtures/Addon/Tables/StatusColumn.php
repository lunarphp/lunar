<?php

namespace Lunar\Tests\Panel\Fixtures\Addon\Tables;

use Lunar\Panel\Tables\TableColumn;

class StatusColumn extends TableColumn
{
    public function key(): string
    {
        return 'status';
    }

    public function header(): string
    {
        return 'Status';
    }
}
