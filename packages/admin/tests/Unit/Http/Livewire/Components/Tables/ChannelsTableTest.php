<?php

namespace GetCandy\Hub\Tests\Unit\Http\Livewire\Components;

use GetCandy\Hub\Http\Livewire\Components\Tables\ChannelsTable;
use GetCandy\Hub\Tests\TestCase;
use GetCandy\Models\Channel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

/**
 * @group hub.tables
 */
class ChannelsTableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_mount_table()
    {
        Livewire::test(ChannelsTable::class);
    }

    /** @test */
    public function can_see_table_data()
    {
        $records = Channel::factory(10)->create();
        Livewire::test(ChannelsTable::class)->assertCanSeeTableRecords($records);
    }

    /** @test */
    public function can_see_base_columns()
    {
        Channel::factory(10)->create();

        $columns = [
            'default',
            'name',
            'handle',
            'url',
        ];

        $table = Livewire::test(ChannelsTable::class);

        foreach ($columns as $column) {
            $table->assertCanRenderTableColumn($column);
        }
    }
}
