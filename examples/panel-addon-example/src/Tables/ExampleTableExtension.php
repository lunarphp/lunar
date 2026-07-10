<?php

namespace LunarPanelExample\Tables;

use Lunar\Panel\Tables\TableExtension;

class ExampleTableExtension extends TableExtension
{
    public function columns(): array
    {
        return [ExampleColumn::class];
    }
}
