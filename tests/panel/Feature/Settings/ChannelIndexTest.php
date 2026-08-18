<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');
});

test('the channels index renders with the real channel list', function () {
    $default = Channel::factory()->create(['name' => 'Webstore', 'default' => true]);
    $other = Channel::factory()->create(['name' => 'Retail', 'default' => false]);

    $this->get(route('panel.settings.channels.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/channels/Index')
            ->has('channels.data', 2)
            ->where('channels.data.0.name', 'Retail')
            ->where('channels.data.1.name', 'Webstore')
            ->where('channels.data.1.default', true)
            ->where('channels.data.0.default', false)
            ->has('urls.store')
        );

    expect($default->fresh()->default)->toBeTrue();
    expect($other->fresh()->default)->toBeFalse();
});

test('channels carry first-party row actions, with delete omitted for the default channel', function () {
    // Order by name: Retail (non-default) then Webstore (default).
    Channel::factory()->create(['name' => 'Webstore', 'default' => true]);
    Channel::factory()->create(['name' => 'Retail', 'default' => false]);

    $this->get(route('panel.settings.channels.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('tableActions', fn ($actions) => collect($actions)->pluck('key')->all() === ['edit', 'delete'])
            // Non-default channel: both edit and delete resolve a url.
            ->where('channels.data.0._actions', fn ($actions) => isset($actions['edit'], $actions['delete']))
            // Default channel: edit only, delete is protected/omitted.
            ->where('channels.data.1._actions', fn ($actions) => isset($actions['edit']) && ! isset($actions['delete']))
        );
});

test('the default flag is serialized as a real boolean', function () {
    Channel::factory()->create(['name' => 'Webstore', 'default' => true]);

    $this->get(route('panel.settings.channels.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('channels.data.0.default', true)
            ->whereType('channels.data.0.default', 'boolean')
        );
});
