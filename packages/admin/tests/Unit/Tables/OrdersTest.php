<?php

namespace Lunar\Hub\Tests\Unit\Menu;

use Lunar\Hub\DataTransferObjects\TableColumn;
use Lunar\Hub\DataTransferObjects\TableFilter;
use Lunar\Hub\Facades\OrdersTable;
use Lunar\Hub\Tests\TestCase;

/**
 * @group hub.tables
 */
class OrdersTest extends TestCase
{
    /** @test */
    public function can_fetch_and_set_columns()
    {
        $this->assertCount(0, OrdersTable::getColumns());

        OrdersTable::addColumn('Test');

        $this->assertCount(1, OrdersTable::getColumns());

        $this->assertInstanceOf(TableColumn::class, OrdersTable::getColumns()->first());
    }

    /** @test */
    public function can_fetch_and_set_filters()
    {
        $this->assertCount(0, OrdersTable::getFilters());

        OrdersTable::addFilter('Test', 'test');

        $this->assertCount(1, OrdersTable::getFilters());

        $this->assertInstanceOf(TableFilter::class, OrdersTable::getFilters()->first());
    }
}
