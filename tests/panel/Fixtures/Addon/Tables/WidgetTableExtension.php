<?php

namespace Lunar\Tests\Panel\Fixtures\Addon\Tables;

use Lunar\Panel\Tables\TableExtension;

class WidgetTableExtension extends TableExtension
{
    public function columns(): array
    {
        return [StatusColumn::class];
    }
}
