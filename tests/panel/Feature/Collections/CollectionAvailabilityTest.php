<?php

use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Models\EditDraft;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Language::factory()->create(['default' => true, 'code' => 'en']);

    $this->channel = Channel::factory()->create(['default' => true]);
    $this->customerGroup = CustomerGroup::factory()->create();

    $this->collection = Collection::factory()->create([
        'collection_group_id' => CollectionGroup::factory(),
    ]);
});

it('exposes availability rows and values on the edit page', function () {
    $this->get(route('panel.collections.edit', $this->collection))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('availability.channels', 1)
            ->where('availability.channels.0.field', 'channel:'.$this->channel->id)
            ->has('availability.customer_groups', 1)
            ->has('availabilityValues.channel:'.$this->channel->id)
        );
});

it('drafts a channel availability row', function () {
    $field = 'channel:'.$this->channel->id;

    $this->patchJson(route('panel.collections.draft.update', $this->collection), [
        'data' => [
            $field => ['enabled' => false, 'starts_at' => null, 'ends_at' => null],
        ],
    ])->assertOk();

    expect(EditDraft::sole()->data[$field]['enabled'])->toBeFalse();
});

it('commits drafted availability through the pivot sync, keeping untouched rows', function () {
    $otherChannel = Channel::factory()->create();

    // The default channel starts enabled via HasChannels' created hook.
    $this->collection->channels()->sync([
        $this->channel->id => ['enabled' => true, 'starts_at' => now()->subDay(), 'ends_at' => null],
        $otherChannel->id => ['enabled' => true, 'starts_at' => now()->subDay(), 'ends_at' => null],
    ]);

    $this->patchJson(route('panel.collections.draft.update', $this->collection), [
        'data' => [
            'channel:'.$this->channel->id => ['enabled' => false, 'starts_at' => null, 'ends_at' => null],
            'customer_group:'.$this->customerGroup->id => [
                'enabled' => true,
                'visible' => false,
                'starts_at' => '2027-01-01 09:00:00',
                'ends_at' => null,
            ],
        ],
    ])->assertOk();

    $this->postJson(route('panel.collections.draft.commit', $this->collection), [
        'data' => [
            'channel:'.$this->channel->id => ['enabled' => false, 'starts_at' => null, 'ends_at' => null],
            'customer_group:'.$this->customerGroup->id => [
                'enabled' => true,
                'visible' => false,
                'starts_at' => '2027-01-01 09:00:00',
                'ends_at' => null,
            ],
        ],
        'rebase' => [],
    ])->assertOk();

    $channelPivot = $this->collection->channels()->find($this->channel->id)->pivot;
    $untouchedPivot = $this->collection->channels()->find($otherChannel->id)->pivot;
    $groupPivot = $this->collection->customerGroups()->find($this->customerGroup->id)->pivot;

    expect((bool) $channelPivot->enabled)->toBeFalse()
        // The drafted row never mentioned the other channel; it survives the sync.
        ->and((bool) $untouchedPivot->enabled)->toBeTrue()
        ->and((bool) $groupPivot->enabled)->toBeTrue()
        ->and((bool) $groupPivot->visible)->toBeFalse()
        ->and($groupPivot->starts_at)->not->toBeNull();
});

it('stores a channel schedule sent with an offset as the matching instant', function () {
    $field = 'channel:'.$this->channel->id;

    // 1 Jul 09:00 in London (BST, UTC+1).
    $this->postJson(route('panel.collections.draft.commit', $this->collection), [
        'data' => [$field => ['enabled' => true, 'starts_at' => '2027-07-01T09:00:00+01:00', 'ends_at' => null]],
        'rebase' => [],
    ])->assertOk();

    $pivot = $this->collection->channels()->find($this->channel->id)->pivot;

    expect((string) $pivot->starts_at)->toBe('2027-07-01 08:00:00');

    // The edit page hands the schedule back as an instant the browser can read.
    $this->get(route('panel.collections.edit', $this->collection))
        ->assertInertia(fn ($page) => $page
            ->where("availabilityValues.{$field}.starts_at", '2027-07-01T08:00:00+00:00')
        );
});

it('rejects a malformed availability value', function () {
    $this->patchJson(route('panel.collections.draft.update', $this->collection), [
        'data' => [
            'channel:'.$this->channel->id => ['enabled' => 'maybe', 'starts_at' => 'not-a-date'],
        ],
    ]);

    $this->postJson(route('panel.collections.draft.commit', $this->collection), [
        'data' => [
            'channel:'.$this->channel->id => ['enabled' => 'maybe', 'starts_at' => 'not-a-date'],
        ],
        'rebase' => [],
    ])->assertUnprocessable();
});
