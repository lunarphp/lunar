<?php

namespace GetCandy\LivewireTables\Tests\Unit\Columns;

use GetCandy\LivewireTables\Components\Columns\TextColumn;
use GetCandy\LivewireTables\Tests\TestCase;
use Livewire\Livewire;

/**
 * @group tables
 */
class TextColumnTest extends TestCase
{
    /** @test */
    public function can_instantiate_class()
    {
        Livewire::test(TextColumn::class)->assertViewIs('tables::columns.base');
    }

    /** @test */
    public function can_set_properties()
    {
        Livewire::test(TextColumn::class)
            ->call('heading', 'Foo')
            ->assertSet('heading', 'Foo');
    }
}
