<?php

use Lunar\Core\Models\Collection;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Models\EditDraft;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');

    Language::factory()->create(['default' => true, 'code' => 'en']);

    $this->collection = Collection::factory()->create([
        'collection_group_id' => CollectionGroup::factory(),
        'name' => ['en' => 'Outerwear'],
        'handle' => 'outerwear',
        'short_description' => collect(['en' => 'Original blurb.']),
    ]);
});

it('autosaves changed fields to a draft', function () {
    $this->patchJson(route('panel.collections.draft.update', $this->collection), [
        'data' => ['name' => ['en' => 'Outerwear & Coats']],
    ])->assertOk();

    $draft = EditDraft::sole();

    expect($draft->data)->toBe(['name' => ['en' => 'Outerwear & Coats']])
        ->and($draft->base_snapshot)->toHaveKey('name', ['en' => 'Outerwear']);
});

it('drafts the sort rule as a scalar field', function () {
    $this->patchJson(route('panel.collections.draft.update', $this->collection), [
        'data' => ['sort' => 'min_price:desc'],
    ])->assertOk();

    expect(EditDraft::sole()->data)->toBe(['sort' => 'min_price:desc']);
});

it('rejects fields outside the draftable set', function () {
    $this->patchJson(route('panel.collections.draft.update', $this->collection), [
        'data' => ['collection_group_id' => 999],
    ])->assertUnprocessable();
});

it('commits a draft through the update action', function () {
    $this->patchJson(route('panel.collections.draft.update', $this->collection), [
        'data' => [
            'name' => ['en' => 'Outerwear & Coats'],
            'status' => 'published',
            'sort' => 'sku:desc',
        ],
    ]);

    $this->postJson(route('panel.collections.draft.commit', $this->collection), [
        'data' => [],
        'rebase' => [],
    ])->assertOk();

    $this->collection->refresh();

    expect($this->collection->translate('name'))->toBe('Outerwear & Coats')
        ->and($this->collection->status->getValue())->toBe('published')
        ->and($this->collection->sort)->toBe('sku:desc')
        ->and(EditDraft::count())->toBe(0);
});

it('rejects a commit with an invalid drafted handle', function () {
    Collection::factory()->create(['handle' => 'taken']);

    $this->patchJson(route('panel.collections.draft.update', $this->collection), [
        'data' => ['handle' => 'taken'],
    ])->assertOk();

    $this->postJson(route('panel.collections.draft.commit', $this->collection), [
        'data' => [],
        'rebase' => [],
    ])->assertUnprocessable();

    expect($this->collection->fresh()->handle)->toBe('outerwear');
});

it('surfaces a conflict when the record moved under the draft', function () {
    $this->patchJson(route('panel.collections.draft.update', $this->collection), [
        'data' => ['name' => ['en' => 'Mine']],
    ]);

    // Another editor commits a competing change directly.
    $this->collection->update(['name' => ['en' => 'Theirs']]);

    $this->postJson(route('panel.collections.draft.commit', $this->collection), [
        'data' => [],
        'rebase' => [],
    ])->assertConflict()
        ->assertJsonPath('conflicts.0.key', 'name');
});
