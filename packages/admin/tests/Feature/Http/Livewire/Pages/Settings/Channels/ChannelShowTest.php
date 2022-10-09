<?php

namespace Lunar\Hub\Tests\Feature\Http\Livewire\Pages\Settings\Channels;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Hub\Tests\TestCase;
use Lunar\Models\Channel;

/**
 * @group channels
 */
class ChannelShowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cant_view_page_as_guest()
    {
        $channel = Channel::factory()->create();
        $this->get("/hub/settings/channels/{$channel->id}")
            ->assertRedirect(
                route('hub.login')
            );
    }
}
