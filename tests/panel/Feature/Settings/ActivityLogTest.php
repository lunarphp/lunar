<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    // The panel TestCase disables activity logging globally; these tests are about the log.
    activity()->enableLogging();

    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');
});

test('the activity log renders logged model activity', function () {
    // Channel updates log activity through the LogsActivity concern.
    $channel = Channel::factory()->create(['name' => 'Webstore']);
    $channel->update(['name' => 'Retail']);

    $this->get(route('panel.settings.activity-log.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/activity-log/Index')
            ->where('activities.total', fn ($total) => $total >= 1)
            ->has('activities.data.0.description')
            ->has('activities.data.0.subject_type')
            ->has('subjectTypes')
            ->has('events')
        );
});

test('the activity log exposes the standard table extension seams', function () {
    $channel = Channel::factory()->create(['name' => 'Webstore']);
    $channel->update(['name' => 'Retail']);

    $this->get(route('panel.settings.activity-log.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('columns', fn ($columns) => collect($columns)->pluck('key')->all() === ['description', 'subject', 'causer_name', 'created_at'])
            ->has('tableActions')
            ->has('tableBulkActions')
            ->has('tableFilters')
            ->has('activities.data.0._actions')
        );
});

test('the activity log can be filtered by event', function () {
    $channel = Channel::factory()->create(['name' => 'Webstore']);
    $channel->update(['name' => 'Retail']);

    $this->get(route('panel.settings.activity-log.index', ['event' => 'updated']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('activities.data', fn ($rows) => collect($rows)->every(fn ($row) => $row['event'] === 'updated'))
        );
});

test('activity rows carry the causing staff member when authenticated', function () {
    $channel = Channel::factory()->create(['name' => 'Webstore', 'default' => false]);

    $this->put(route('panel.settings.channels.update', $channel), [
        'name' => 'Retail',
    ]);

    $this->get(route('panel.settings.activity-log.index', ['event' => 'updated']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('activities.data.0.causer_name', $this->staff->full_name)
        );
});
