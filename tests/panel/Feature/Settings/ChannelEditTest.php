<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');
});

test('the channel edit screen renders with the channel data', function () {
    $channel = Channel::factory()->create([
        'name' => 'Retail',
        'url' => 'https://retail.example.com',
        'default' => false,
    ]);

    $this->get(route('panel.settings.channels.edit', $channel))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/channels/Edit')
            ->where('channel.id', $channel->id)
            ->where('channel.name', 'Retail')
            ->where('channel.handle', $channel->handle)
            ->where('channel.url', 'https://retail.example.com')
            ->where('channel.default', false)
            ->whereType('channel.default', 'boolean')
            ->where('hasOrderHistory', false)
            ->has('urls.update')
            ->has('urls.destroy')
            ->has('urls.index')
        );
});

test('the edit screen flags order history', function () {
    $channel = Channel::factory()->create(['default' => false]);
    Order::factory()->create(['channel_id' => $channel->id]);

    $this->get(route('panel.settings.channels.edit', $channel))
        ->assertInertia(fn (Assert $page) => $page->where('hasOrderHistory', true));
});

test('a channel can be updated', function () {
    $channel = Channel::factory()->create([
        'name' => 'Retail',
        'url' => 'https://old.example.com',
        'default' => false,
        'status' => 'active',
    ]);

    $this->put(route('panel.settings.channels.update', $channel), [
        'name' => 'Retail Store',
        'url' => 'https://new.example.com',
        'status' => 'inactive',
    ])->assertRedirect(route('panel.settings.channels.index'))
        ->assertSessionHas('success');

    $channel->refresh();

    expect($channel->name)->toBe('Retail Store');
    expect($channel->handle)->toBe('retail-store');
    expect($channel->url)->toBe('https://new.example.com');
    expect((string) $channel->status)->toBe('inactive');
});

test('renaming a channel onto another channel handle is rejected', function () {
    Channel::factory()->create(['name' => 'Webstore', 'handle' => 'webstore']);
    $channel = Channel::factory()->create(['name' => 'Retail', 'handle' => 'retail']);

    $this->put(route('panel.settings.channels.update', $channel), [
        'name' => 'Webstore',
    ])->assertSessionHasErrors('name');

    expect($channel->fresh()->handle)->toBe('retail');
});

test('a channel can keep its own name on update', function () {
    $channel = Channel::factory()->create(['name' => 'Retail', 'handle' => 'retail']);

    $this->put(route('panel.settings.channels.update', $channel), [
        'name' => 'Retail',
    ])->assertSessionHasNoErrors()
        ->assertRedirect(route('panel.settings.channels.index'))
        ->assertSessionHas('success');
});

test('updating a channel to default un-defaults whichever channel was default', function () {
    $default = Channel::factory()->create(['name' => 'Webstore', 'default' => true]);
    $channel = Channel::factory()->create(['name' => 'Retail', 'default' => false]);

    $this->put(route('panel.settings.channels.update', $channel), [
        'name' => 'Retail',
        'default' => true,
    ])->assertRedirect(route('panel.settings.channels.index'));

    expect($default->fresh()->default)->toBeFalse();
    expect($channel->fresh()->default)->toBeTrue();
    expect(Channel::where('default', true)->count())->toBe(1);
});

test('unsetting default on the default channel is rejected with a flash error', function () {
    $channel = Channel::factory()->create(['name' => 'Webstore', 'default' => true]);

    $this->from(route('panel.settings.channels.edit', $channel))
        ->put(route('panel.settings.channels.update', $channel), [
            'name' => 'Webstore',
            'default' => false,
        ])->assertRedirect(route('panel.settings.channels.edit', $channel))
        ->assertSessionHas('error', __('panel::channels.default_unset_blocked'));

    expect($channel->fresh()->default)->toBeTrue();
});

test('the default channel cannot be deleted and shows a flash error', function () {
    $channel = Channel::factory()->create(['default' => true]);

    $this->from(route('panel.settings.channels.edit', $channel))
        ->delete(route('panel.settings.channels.destroy', $channel))
        ->assertRedirect(route('panel.settings.channels.edit', $channel))
        ->assertSessionHas('error', __('panel::channels.delete_blocked_default'));

    expect(Channel::find($channel->id))->not->toBeNull();
});

test('a channel with no order history can be deleted', function () {
    $channel = Channel::factory()->create(['default' => false]);

    $this->delete(route('panel.settings.channels.destroy', $channel))
        ->assertRedirect(route('panel.settings.channels.index'))
        ->assertSessionHas('success');

    expect(Channel::find($channel->id))->toBeNull();
});

test('a channel with order history cannot be deleted and shows a flash error', function () {
    $channel = Channel::factory()->create(['default' => false]);
    Order::factory()->create(['channel_id' => $channel->id]);

    $this->from(route('panel.settings.channels.edit', $channel))
        ->delete(route('panel.settings.channels.destroy', $channel))
        ->assertRedirect(route('panel.settings.channels.edit', $channel))
        ->assertSessionHas('error', 'Cannot delete a channel with order history.');

    expect(Channel::find($channel->id))->not->toBeNull();
});
