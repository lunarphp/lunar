<?php

use Lunar\Core\DiscountTypes\PercentageOff;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Models\EditDraft;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');

    $this->discount = Discount::factory()->create([
        'name' => 'Winter Sale',
        'handle' => 'winter_sale',
        'type' => PercentageOff::class,
        'priority' => 1,
        'data' => ['percentage' => 10],
    ]);
});

it('autosaves changed fields to a draft', function () {
    $this->patchJson(route('panel.discounts.draft.update', $this->discount), [
        'data' => ['name' => 'Winter Clearance'],
    ])->assertOk();

    $draft = EditDraft::sole();

    expect($draft->data)->toBe(['name' => 'Winter Clearance'])
        ->and($draft->base_snapshot)->toHaveKey('name', 'Winter Sale');
});

it('upper-cases a drafted coupon so it matches the stored cast', function () {
    $this->patchJson(route('panel.discounts.draft.update', $this->discount), [
        'data' => ['coupon' => 'winter10'],
    ])->assertOk();

    expect(EditDraft::sole()->data['coupon'])->toBe('WINTER10');
});

it('treats an empty numeric limit as no limit', function () {
    $this->discount->update(['max_uses' => 5]);

    $this->patchJson(route('panel.discounts.draft.update', $this->discount), [
        'data' => ['max_uses' => ''],
    ])->assertOk();

    expect(EditDraft::sole()->data['max_uses'])->toBeNull();
});

it('drafts the type payload as one unit', function () {
    $this->patchJson(route('panel.discounts.draft.update', $this->discount), [
        'data' => ['data' => ['percentage' => 25]],
    ])->assertOk();

    expect(EditDraft::sole()->data['data'])->toBe(['percentage' => 25]);
});

it('rejects fields outside the draftable set', function () {
    $this->patchJson(route('panel.discounts.draft.update', $this->discount), [
        'data' => ['uses' => 500],
    ])->assertUnprocessable();
});

it('commits a draft through the update action', function () {
    $this->patchJson(route('panel.discounts.draft.update', $this->discount), [
        'data' => ['name' => 'Winter Clearance', 'priority' => 30, 'stop' => true],
    ]);

    $this->postJson(route('panel.discounts.draft.commit', $this->discount), [
        'data' => [],
        'rebase' => [],
    ])->assertOk();

    $this->discount->refresh();

    expect($this->discount->name)->toBe('Winter Clearance')
        ->and($this->discount->priority)->toBe(30)
        ->and((bool) $this->discount->stop)->toBeTrue()
        ->and(EditDraft::count())->toBe(0);
});

it('commits drafted channel availability', function () {
    $channel = Channel::factory()->create();

    $this->patchJson(route('panel.discounts.draft.update', $this->discount), [
        'data' => ["channel:{$channel->id}" => ['enabled' => true, 'starts_at' => null, 'ends_at' => null]],
    ])->assertOk();

    $this->postJson(route('panel.discounts.draft.commit', $this->discount), [
        'data' => [],
        'rebase' => [],
    ])->assertOk();

    $pivot = $this->discount->channels()->where('channel_id', $channel->id)->first()->pivot;

    expect((bool) $pivot->enabled)->toBeTrue();
});

it('rejects an invalid merged payload at commit', function () {
    Discount::factory()->create(['handle' => 'taken']);

    $this->patchJson(route('panel.discounts.draft.update', $this->discount), [
        'data' => ['handle' => 'taken'],
    ])->assertOk();

    $this->postJson(route('panel.discounts.draft.commit', $this->discount), [
        'data' => [],
        'rebase' => [],
    ])->assertUnprocessable();

    expect($this->discount->refresh()->handle)->toBe('winter_sale');
});
