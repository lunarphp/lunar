<?php

namespace LunarPanelExample\Tables;

use Lunar\Panel\Tables\TableColumn;

class ExampleColumn extends TableColumn
{
    public function key(): string
    {
        return 'id';
    }

    public function header(): string
    {
        return 'ID (Example Add-on)';
    }
}
