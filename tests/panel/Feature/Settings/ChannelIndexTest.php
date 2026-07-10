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
            ->has('channels', 2)
            ->where('channels.0.name', 'Retail')
            ->where('channels.1.name', 'Webstore')
            ->where('channels.1.default', true)
            ->where('channels.0.default', false)
            ->has('urls.store')
        );

    expect($default->fresh()->default)->toBeTrue();
    expect($other->fresh()->default)->toBeFalse();
});

test('the default flag is serialized as a real boolean', function () {
    Channel::factory()->create(['name' => 'Webstore', 'default' => true]);

    $this->get(route('panel.settings.channels.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('channels.0.default', true)
            ->whereType('channels.0.default', 'boolean')
        );
});
