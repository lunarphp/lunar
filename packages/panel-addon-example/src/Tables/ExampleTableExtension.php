<?php

namespace LunarPanelExample\Tables;

use Lunar\Panel\Tables\TableExtension;

class ExampleTableExtension extends TableExtension
{
    public function columns(): array
    {
        return [ExampleColumn::class];
    }

    public function filters(): array
    {
        return [HasAccountRefFilter::class];
    }

    public function actions(): array
    {
        return [PingRowAction::class];
    }

    public function bulkActions(): array
    {
        return [PingBulkAction::class];
    }
}
