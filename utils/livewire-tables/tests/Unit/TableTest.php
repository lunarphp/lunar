<?php

namespace GetCandy\LivewireTables\Tests\Unit;

use GetCandy\LivewireTables\Components\Table;
use GetCandy\LivewireTables\Support\TableBuilder;
use GetCandy\LivewireTables\Tests\TestCase;
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
