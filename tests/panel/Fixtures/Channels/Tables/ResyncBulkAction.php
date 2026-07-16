<?php

namespace Lunar\Tests\Panel\Fixtures\Channels\Tables;

use Lunar\Panel\Tables\TableBulkAction;

class ResyncBulkAction extends TableBulkAction
{
    public function key(): string
    {
        return 'fixture-resync';
    }

    public function label(): string
    {
        return 'Resync selected';
    }

    public function method(): string
    {
        return 'post';
    }

    public function url(): ?string
    {
        return '/fixture/channels/resync';
    }
}
