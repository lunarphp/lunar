<?php

namespace Lunar\Tests\Panel\Fixtures\Channels\Tables;

use Lunar\Panel\Tables\TableExtension;

class ChannelFixtureTableExtension extends TableExtension
{
    public function columns(): array
    {
        return [HandleLengthColumn::class];
    }

    public function bulkActions(): array
    {
        return [ResyncBulkAction::class];
    }
}
