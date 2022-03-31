<?php

namespace GetCandy\Tests\Unit\Models;

use GetCandy\Models\Channel;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChannelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_a_channel()
    {
        $channel = Channel::factory()->create([
            'name'    => 'Webstore',
            'handle'  => 'webstore',
            'default' => true,
            'url'     => 'http://mystore.test',
        ]);

        $this->assertEquals('Webstore', $channel->name);
        $this->assertEquals('webstore', $channel->handle);
        $this->assertTrue($channel->default);
        $this->assertEquals('http://mystore.test', $channel->url);
    }

    /** @test */
    public function changes_are_recorded_in_activity_log()
    {
        activity()->enableLogging();

        $channel = Channel::factory()->create([
            'name' => 'Webstore',
        ]);

        $channel->update([
            'name' => 'Foobar',
        ]);

        $log = $channel->activities()->whereEvent('updated')->first();

        $this->assertNotNull($log);
    }
}
