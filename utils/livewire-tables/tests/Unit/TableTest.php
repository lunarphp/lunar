<?php

namespace Lunar\LivewireTables\Tests\Unit;

use Livewire\Livewire;
use Lunar\LivewireTables\Components\Table;
use Lunar\LivewireTables\Support\TableBuilder;
use Lunar\LivewireTables\Tests\TestCase;

/**
 * @group tables
 */
class TableTest extends TestCase
{
    /** @test */
    public function can_instantiate_class()
    {
        Livewire::test(Table::class)->assertViewIs('l-tables::index');
    }

    /** @test */
    public function table_builder_is_attached()
    {
        Livewire::test(Table::class)
            ->assertSet('tableBuilder', new TableBuilder);
    }
}
