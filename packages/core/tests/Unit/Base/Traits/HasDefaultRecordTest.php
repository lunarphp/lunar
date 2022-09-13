<?php

namespace Lunar\Tests\Unit\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Channel;
use Lunar\Tests\TestCase;

/**
 * @group defaultrec
 */
class HasDefaultRecordTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_get_default_record_with_scope()
    {
        $defaultChannel = Channel::factory()->create([
            'default' => true,
        ]);

        Channel::factory(10)->create([
            'default' => false,
        ]);

        $this->assertEquals($defaultChannel->id, Channel::default()->first()->id);
    }

    /** @test */
    public function can_get_default_record_with_static_helper()
    {
        $defaultChannel = Channel::factory()->create([
            'default' => true,
        ]);

        Channel::factory(10)->create([
            'default' => false,
        ]);

        $this->assertEquals($defaultChannel->id, Channel::getDefault()->id);
    }
}
