<?php

namespace Lunar\LivewireTables\Tests\Unit;

use Lunar\LivewireTables\Components\Table;
use Lunar\LivewireTables\Support\TableBuilder;
use Lunar\LivewireTables\Tests\TestCase;
use Livewire\Livewire;

/**
 * @group tables
 */
class TableTest extends TestCase
{
    /** @test */
    public function can_instantiate_class()
    {
        Livewire::test(Table::class)->assertViewIs('tables::index');
    }

    /** @test */
    public function table_builder_is_attached()
    {
        Livewire::test(Table::class)
            ->assertSet('tableBuilder', new TableBuilder);
    }
}
