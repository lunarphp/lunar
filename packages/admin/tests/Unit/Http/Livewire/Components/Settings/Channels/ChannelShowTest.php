<?php

namespace GetCandy\Hub\Tests\Unit\Http\Livewire\Components;

use GetCandy\Hub\Http\Livewire\Components\Settings\Channels\ChannelShow;
use GetCandy\Hub\Tests\Stubs\User;
use GetCandy\Hub\Tests\TestCase;
use GetCandy\Models\Channel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

class ChannelShowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_update_channel()
    {
        $user = User::create([
            'name'              => 'Test User',
            'email'             => 'test@domain.com',
            'email_verified_at' => now(),
            'password'          => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token'    => \Illuminate\Support\Str::random(10),
        ]);

        $channel = Channel::factory()->create([
            'default' => false,
        ]);

        $this->actingAs($user);

        Livewire::test(ChannelShow::class, [
            'channel' => $channel,
        ])
            ->set('channel.name', 'Some channel name')
            ->set('channel.handle', 'some-handle')
            ->set('channel.url', 'http://google.co.uk')
            ->set('channel.default', 1)
            ->call('update');

        $this->assertFalse($channel->default);

        $channel = $channel->refresh();

        $this->assertEquals('Some channel name', $channel->name);
        $this->assertEquals('some-handle', $channel->handle);
        $this->assertEquals('http://google.co.uk', $channel->url);
        $this->assertTrue((bool) $channel->default);
    }

    /** @test */
    public function event_is_dispatched_on_save()
    {
        $user = User::create([
            'name'              => 'Test User',
            'email'             => 'test@domain.com',
            'email_verified_at' => now(),
            'password'          => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token'    => \Illuminate\Support\Str::random(10),
        ]);

        $channel = Channel::factory()->create([
            'default' => false,
        ]);

        $this->actingAs($user);

        Livewire::test(ChannelShow::class, [
            'channel' => $channel,
        ])
                ->set('channel.name', 'Some channel name')
                ->call('update')
                ->assertRedirect();
    }

    /** @test */
    public function can_channel_has_appropriate_validation_in_place()
    {
        $user = User::create([
            'name'              => 'Test User',
            'email'             => 'test@domain.com',
            'email_verified_at' => now(),
            'password'          => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token'    => \Illuminate\Support\Str::random(10),
        ]);

        Channel::factory()->create([
            'handle' => 'channel-one',
        ]);

        $channel = Channel::factory()->create([
            'handle' => 'channel-two',
        ]);

        $this->actingAs($user);

        Livewire::test(ChannelShow::class, [
            'channel' => $channel,
        ])->set('channel.handle', 'channel-one')
            ->call('update')
            ->assertHasErrors(['channel.handle' => 'unique']);
    }

    /** @test */
    public function cant_delete_a_channel_without_confirming()
    {
        $user = User::create([
            'name'              => 'Test User',
            'email'             => 'test@domain.com',
            'email_verified_at' => now(),
            'password'          => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token'    => \Illuminate\Support\Str::random(10),
        ]);

        $channel = Channel::factory()->create([
            'name'   => 'Some Channel',
            'handle' => 'channel-two',
        ]);

        $this->actingAs($user);

        Livewire::test(ChannelShow::class, [
            'channel' => $channel,
        ])->set('deleteConfirm', 'Another Channel')
            ->call('delete');

        $this->assertNull($channel->refresh()->deleted_at);
    }

    /** @test */
    public function can_softdelete_a_channel()
    {
        $user = User::create([
            'name'              => 'Test User',
            'email'             => 'test@domain.com',
            'email_verified_at' => now(),
            'password'          => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token'    => \Illuminate\Support\Str::random(10),
        ]);

        $channel = Channel::factory()->create([
            'name'   => 'Some Channel',
            'handle' => 'channel-two',
        ]);

        $this->actingAs($user);

        Livewire::test(ChannelShow::class, [
            'channel' => $channel,
        ])->set('deleteConfirm', 'Some Channel')
            ->call('delete');

        $this->assertSoftDeleted($channel->getTable(), [
            'name'   => $channel->name,
            'handle' => $channel->handle,
        ]);
    }

    /** @test */
    public function channel_name_should_not_be_unique()
    {
        $user = User::create([
            'name'              => 'Test User',
            'email'             => 'test@domain.com',
            'email_verified_at' => now(),
            'password'          => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token'    => \Illuminate\Support\Str::random(10),
        ]);

        $channelOne = Channel::factory()->create([
            'name'   => 'Some Channel',
            'handle' => 'channel-one',
        ]);

        $channelTwo = Channel::factory()->create([
            'name'   => 'Some Channel',
            'handle' => 'channel-two',
        ]);

        $this->actingAs($user);

        Livewire::test(ChannelShow::class, [
            'channel' => $channelTwo,
        ])->set('channel.name', $channelOne->name)
                ->call('update');

        $this->assertDatabaseHas((new Channel())->getTable(), [
            'name'   => $channelOne->name,
            'handle' => $channelOne->handle,
        ]);

        $this->assertDatabaseHas((new Channel())->getTable(), [
            'name'   => $channelOne->name,
            'handle' => $channelTwo->handle,
        ]);
    }

    /** @test */
    public function affected_inputs_have_suitable_length_validation()
    {
        $user = User::create([
            'name'              => 'Test User',
            'email'             => 'test@domain.com',
            'email_verified_at' => now(),
            'password'          => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token'    => \Illuminate\Support\Str::random(10),
        ]);

        $this->actingAs($user);

        $channel = Channel::factory()->create();

        Livewire::test(ChannelShow::class, [
            'channel' => $channel,
        ])->set('channel.name', Str::random(260))
            ->set('channel.handle', Str::random(260))
            ->set('channel.url', Str::random(260))
            ->call('update')
            ->assertHasErrors([
                'channel.name'   => 'max',
                'channel.handle' => 'max',
            ]);
    }
}
