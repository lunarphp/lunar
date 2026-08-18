<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\Fixtures\ChannelFixtureTestCase;

uses(ChannelFixtureTestCase::class);

beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');
});

it('merges add-on table extension columns onto the channels index', function () {
    Channel::factory()->create(['handle' => 'webstore']);

    $this->get(route('panel.settings.channels.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('columns', function ($columns) {
                $keys = collect($columns)->pluck('key')->all();

                // First-party columns still come first, extension columns are appended.
                return $keys === ['handle', 'name', 'url', 'status', 'handle_length'];
            })
            // "handle_length" only appears with a real value because the extension
            // column's query() hook was actually applied to the channel query.
            ->where('channels.data.0.handle_length', 8)
        );
});

it('shares add-on bulk actions with the channels index', function () {
    Channel::factory()->create();

    $this->get(route('panel.settings.channels.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('tableBulkActions', fn ($actions) => collect($actions)->pluck('key')->all() === ['fixture-resync'])
        );
});
