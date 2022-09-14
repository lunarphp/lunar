<?php

namespace Lunar\LivewireTables\Tests\Unit\Columns;

use Lunar\LivewireTables\Components\Columns\TextColumn;
use Lunar\LivewireTables\Tests\TestCase;
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
