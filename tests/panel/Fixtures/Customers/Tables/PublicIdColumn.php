<?php

namespace Lunar\Tests\Panel\Fixtures\Customers\Tables;

use Lunar\Panel\Tables\TableColumn;

class PublicIdColumn extends TableColumn
{
    public function key(): string
    {
        return 'public_id';
    }

    public function header(): string
    {
        return 'Public ID';
    }
}
