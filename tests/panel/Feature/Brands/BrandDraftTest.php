<?php

use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Models\EditDraft;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');

    Language::factory()->create(['default' => true]);

    $this->brand = Brand::factory()->create([
        'name' => 'Stark Industries',
        'handle' => 'stark',
        'short_description' => collect(['en' => 'Original blurb.']),
    ]);
});

it('autosaves changed fields to a draft', function () {
    $this->patchJson(route('panel.brands.draft.update', $this->brand), [
        'data' => ['name' => 'Stark Industries Ltd'],
    ])->assertOk();

    $draft = EditDraft::sole();

    expect($draft->data)->toBe(['name' => 'Stark Industries Ltd'])
        ->and($draft->base_snapshot)->toHaveKey('name', 'Stark Industries');
});

it('normalises translation maps when drafting', function () {
    $this->patchJson(route('panel.brands.draft.update', $this->brand), [
        'data' => ['short_description' => ['fr' => 'Blurb FR', 'en' => 'Original blurb.', 'de' => '']],
    ])->assertOk();

    expect(EditDraft::sole()->data['short_description'])->toBe([
        'en' => 'Original blurb.',
        'fr' => 'Blurb FR',
    ]);
});

it('rejects fields outside the draftable set', function () {
    $this->patchJson(route('panel.brands.draft.update', $this->brand), [
        'data' => ['public_id' => 'nope'],
    ])->assertUnprocessable();
});

it('commits a draft through the update action', function () {
    $this->patchJson(route('panel.brands.draft.update', $this->brand), [
        'data' => ['name' => 'Stark Industries Ltd', 'status' => 'draft'],
    ]);

    $this->postJson(route('panel.brands.draft.commit', $this->brand), [
        'data' => [],
        'rebase' => [],
    ])->assertOk();

    $this->brand->refresh();

    expect($this->brand->name)->toBe('Stark Industries Ltd')
        ->and($this->brand->status->getValue())->toBe('draft')
        ->and(EditDraft::count())->toBe(0);
});

it('rejects an invalid merged payload at commit', function () {
    Brand::factory()->create(['handle' => 'taken']);

    $this->patchJson(route('panel.brands.draft.update', $this->brand), [
        'data' => ['handle' => 'taken'],
    ])->assertOk();

    $this->postJson(route('panel.brands.draft.commit', $this->brand), [
        'data' => [],
        'rebase' => [],
    ])->assertUnprocessable();
});

it('detects a conflict when the same field changed underneath the draft', function () {
    $this->patchJson(route('panel.brands.draft.update', $this->brand), [
        'data' => ['name' => 'Mine'],
    ]);

    $this->brand->update(['name' => 'Theirs']);

    $response = $this->postJson(route('panel.brands.draft.commit', $this->brand), [
        'data' => [],
        'rebase' => [],
    ]);

    $response->assertConflict()
        ->assertJsonPath('conflicts.0.key', 'name')
        ->assertJsonPath('conflicts.0.mine', 'Mine')
        ->assertJsonPath('conflicts.0.theirs', 'Theirs');
});
