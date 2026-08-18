<?php

namespace Lunar\Tests\Panel\Fixtures\Channels\Tables;

use Illuminate\Database\Eloquent\Builder;
use Lunar\Panel\Tables\TableColumn;

/**
 * Demonstrates a column that needs a computed value hooked onto the query —
 * "handle_length" only exists on the row if applyColumnQueries() ran.
 */
class HandleLengthColumn extends TableColumn
{
    public function key(): string
    {
        return 'handle_length';
    }

    public function header(): string
    {
        return 'Handle length';
    }

    public function query(Builder $query): void
    {
        $query->selectRaw('*, length(handle) as handle_length');
    }
}
