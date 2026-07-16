<?php

use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');
});

test('a channel can be created', function () {
    $this->post(route('panel.settings.channels.store'), [
        'name' => 'Retail',
        'url' => 'https://retail.example.com',
        'status' => 'active',
    ])->assertRedirect(route('panel.settings.channels.index'))
        ->assertSessionHas('success');

    $channel = Channel::where('name', 'Retail')->first();

    expect($channel)->not->toBeNull();
    expect($channel->handle)->toBe('retail');
    expect($channel->url)->toBe('https://retail.example.com');
    expect((string) $channel->status)->toBe('active');
    expect($channel->default)->toBeFalse();
});

test('creating a second channel as default un-defaults the first', function () {
    $first = Channel::factory()->create(['name' => 'Webstore', 'default' => true]);

    $this->post(route('panel.settings.channels.store'), [
        'name' => 'Retail',
        'default' => true,
    ])->assertRedirect(route('panel.settings.channels.index'));

    $second = Channel::where('name', 'Retail')->first();

    expect($first->fresh()->default)->toBeFalse();
    expect($second->fresh()->default)->toBeTrue();
    expect(Channel::where('default', true)->count())->toBe(1);
});

test('name is required', function () {
    $this->post(route('panel.settings.channels.store'), [
        'url' => 'https://retail.example.com',
    ])->assertSessionHasErrors('name');

    expect(Channel::count())->toBe(0);
});

test('status must be a valid channel state', function () {
    $this->post(route('panel.settings.channels.store'), [
        'name' => 'Retail',
        'status' => 'not-a-real-state',
    ])->assertSessionHasErrors('status');
});
