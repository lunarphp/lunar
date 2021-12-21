<?php

namespace GetCandy\Hub\Tests\Unit\Http\Livewire\Components;

use GetCandy\Hub\Http\Livewire\Components\Settings\Channels\ChannelCreate;
use GetCandy\Hub\Http\Livewire\Components\Settings\Channels\ChannelShow;
use GetCandy\Hub\Tests\Stubs\User;
use GetCandy\Hub\Tests\TestCase;
use GetCandy\Models\Channel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Livewire;

class ChannelCreateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_channel()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@domain.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);

        $this->actingAs($user);

        Livewire::test(ChannelCreate::class, [
            'channel' => new Channel,
        ])
            ->set('channel.name', 'Some channel name')
            ->set('channel.handle', 'some-handle')
            ->set('channel.url', 'http://google.co.uk')
            ->set('channel.default', 1)
            ->call('create');

        $this->assertDatabaseHas((new Channel)->getTable(), [
            'name' => 'Some channel name',
            'handle' => 'some-handle',
            'url' => 'http://google.co.uk',
            'default' => '1',
        ]);
    }

    /** @test */
    public function can_channel_has_appropriate_validation_in_place()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@domain.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);

        $this->actingAs($user);

        Livewire::test(ChannelCreate::class, [
            'channel' => new Channel,
        ])->call('create')
            ->assertHasErrors([
                'channel.name' => 'required',
                'channel.handle' => 'required',
            ]);
    }

    /** @test */
    public function channel_name_should_not_be_unique()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@domain.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);

        $channel = Channel::factory()->create([
            'name' => 'Some Channel',
            'handle' => 'channel-one',
        ]);

        $this->actingAs($user);

        Livewire::test(ChannelCreate::class, [
            'channel' => new Channel,
        ])->set('channel.default', true)
        ->set('channel.name', $channel->name)
        ->set('channel.handle', 'channel-two')
        ->call('create')
        ->assertRedirect();

        $this->assertDatabaseHas((new Channel)->getTable(), [
            'name' => $channel->name,
            'handle' => 'channel-one',
        ]);

        $this->assertDatabaseHas((new Channel)->getTable(), [
            'name' => $channel->name,
            'handle' => 'channel-two',
        ]);
    }

    /** @test */
    public function affected_inputs_have_suitable_length_validation()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@domain.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);

        $this->actingAs($user);

        Livewire::test(ChannelCreate::class, [
            'channel' => new Channel,
        ])->set('channel.name', Str::random(260))
            ->set('channel.handle', Str::random(260))
            ->set('channel.url', Str::random(260))
            ->call('create')
            ->assertHasErrors([
                'channel.name' => 'max',
                'channel.handle' => 'max',
            ]);
    }
}
