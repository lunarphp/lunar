<?php

namespace Lunar\Tests\Panel\Fixtures\Customers\Tables;

use Illuminate\Database\Eloquent\Builder;
use Lunar\Panel\Tables\TableColumn;

/**
 * Demonstrates a column that needs a computed value hooked onto the query —
 * "addresses_count" only exists on the row if applyColumnQueries() ran.
 */
class AddressCountColumn extends TableColumn
{
    public function key(): string
    {
        return 'addresses_count';
    }

    public function header(): string
    {
        return 'Addresses';
    }

    public function query(Builder $query): void
    {
        $query->withCount('addresses');
    }
}
