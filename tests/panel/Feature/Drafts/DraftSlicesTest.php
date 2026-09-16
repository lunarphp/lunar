<?php

use Illuminate\Database\Eloquent\Model;
use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Drafts\ComposedDraftResource;
use Lunar\Panel\Drafts\DraftSlice;
use Lunar\Panel\Models\EditDraft;
use Lunar\Panel\PanelManager;
use Lunar\Panel\Sections\Catalog\BrandDraftResource;
use Lunar\Panel\Sections\Sales\CustomerDraftResource;
use Lunar\Tests\Panel\Fixtures\Drafts\MemoSlice;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

class RivalMemoSlice extends MemoSlice {}

class AddonReservedSlice extends DraftSlice
{
    public function model(): string
    {
        return Customer::class;
    }

    public function key(): string
    {
        return 'addon';
    }

    public function fields(Model $record): array
    {
        return [];
    }

    public function currentValues(Model $record): array
    {
        return [];
    }

    public function rules(Model $record): array
    {
        return [];
    }

    public function commit(Model $record, array $values): void {}
}

class BadKeySlice extends AddonReservedSlice
{
    public function key(): string
    {
        return 'Not Valid';
    }
}

beforeEach(function () {
    MemoSlice::$committed = [];
    MemoSlice::$discarded = [];

    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');

    $this->customer = Customer::factory()->create(['first_name' => 'Ada', 'meta' => ['memo' => 'hello']]);

    app(PanelManager::class)->draftSlice(MemoSlice::class);
});

it('composes a resource with its slices under prefixed keys', function () {
    $resource = app(PanelManager::class)->draftableFor($this->customer);

    expect($resource)->toBeInstanceOf(ComposedDraftResource::class)
        ->and($resource->resource())->toBeInstanceOf(CustomerDraftResource::class)
        ->and($resource->fields())->toContain('first_name', 'notes:memo')
        ->and($resource->currentValues($this->customer)['notes:memo'])->toBe('hello')
        ->and($resource->sliceValues($this->customer))->toBe(['notes:memo' => 'hello'])
        ->and($resource->rules($this->customer))->toHaveKey('notes:memo')
        ->and($resource->labels()['notes:memo'])->toBe('Memo');
});

it('returns the plain resource for a model without slices', function () {
    expect(app(PanelManager::class)->draftableFor(Brand::factory()->make()))
        ->toBeInstanceOf(BrandDraftResource::class);
});

it('places extension slices under the reserved addon namespace', function () {
    $manager = app(PanelManager::class)->draftSlice(RivalMemoSlice::class, addon: true);

    expect(array_keys($manager->draftSlicesFor(Customer::class)))->toBe(['notes', 'addon:notes']);
});

it('rejects a second class claiming the same namespace', function () {
    app(PanelManager::class)->draftSlice(RivalMemoSlice::class);
})->throws(InvalidArgumentException::class, 'claimed by both');

it('ignores the same class registering twice', function () {
    $manager = app(PanelManager::class)->draftSlice(MemoSlice::class);

    expect($manager->draftSlicesFor(Customer::class))->toHaveCount(1);
});

it('rejects a first-party slice claiming the addon namespace', function () {
    app(PanelManager::class)->draftSlice(AddonReservedSlice::class);
})->throws(InvalidArgumentException::class, 'reserved [addon] namespace');

it('rejects a malformed namespace', function () {
    app(PanelManager::class)->draftSlice(BadKeySlice::class);
})->throws(InvalidArgumentException::class, 'must match [a-z0-9_-]+');

it('autosaves a slice field under its prefixed key, normalised by the slice', function () {
    $this->patchJson(route('panel.customers.draft.update', $this->customer), [
        'data' => ['first_name' => 'Grace', 'notes:memo' => ''],
    ])->assertOk()->assertJsonPath('data.notes:memo', null);

    expect(EditDraft::sole()->base_snapshot)->toBe(['first_name' => 'Ada', 'notes:memo' => 'hello']);
});

it('validates slice fields under their prefixed key', function () {
    $this->patchJson(route('panel.customers.draft.update', $this->customer), [
        'data' => ['notes:memo' => str_repeat('x', 21)],
    ])->assertOk();

    $this->postJson(route('panel.customers.draft.commit', $this->customer), ['data' => [], 'rebase' => []])
        ->assertUnprocessable()
        ->assertJsonValidationErrorFor('notes:memo');
});

it('commits the resource then the slice inside one transaction', function () {
    $this->patchJson(route('panel.customers.draft.update', $this->customer), [
        'data' => ['first_name' => 'Grace', 'notes:memo' => 'updated'],
    ])->assertOk();

    $this->postJson(route('panel.customers.draft.commit', $this->customer), ['data' => [], 'rebase' => []])
        ->assertOk();

    $this->customer->refresh();

    expect($this->customer->first_name)->toBe('Grace')
        ->and($this->customer->meta['memo'])->toBe('updated')
        ->and(MemoSlice::$committed)->toBe(['slice'])
        ->and(EditDraft::count())->toBe(0)
        // A committed draft is consumed, not discarded.
        ->and(MemoSlice::$discarded)->toBe([]);
});

it('rolls the resource commit back when a slice commit fails', function () {
    $this->patchJson(route('panel.customers.draft.update', $this->customer), [
        'data' => ['first_name' => 'Grace', 'notes:memo' => 'boom'],
    ])->assertOk();

    $this->withoutExceptionHandling();

    try {
        $this->postJson(route('panel.customers.draft.commit', $this->customer), ['data' => [], 'rebase' => []]);
    } catch (RuntimeException $exception) {
        expect($exception->getMessage())->toBe('Memo slice commit failed.');
    }

    expect($this->customer->refresh()->first_name)->toBe('Ada')
        ->and(EditDraft::count())->toBe(1);
});

it('reports a slice conflict with the slice label', function () {
    $this->patchJson(route('panel.customers.draft.update', $this->customer), [
        'data' => ['notes:memo' => 'mine'],
    ])->assertOk();

    $this->customer->update(['meta' => ['memo' => 'theirs']]);

    $this->postJson(route('panel.customers.draft.commit', $this->customer), ['data' => [], 'rebase' => []])
        ->assertConflict()
        ->assertJsonPath('conflicts.0.key', 'notes:memo')
        ->assertJsonPath('conflicts.0.label', 'Memo')
        ->assertJsonPath('conflicts.0.theirs', 'theirs');
});

it('drops stored keys the resource no longer declares instead of blocking the commit', function () {
    EditDraft::factory()->create([
        'draftable_type' => $this->customer->getMorphClass(),
        'draftable_id' => $this->customer->id,
        'staff_id' => $this->staff->id,
        'data' => ['first_name' => 'Grace', 'addon:removed:field' => 'orphan'],
        'base_snapshot' => ['first_name' => 'Ada', 'addon:removed:field' => 'old'],
    ]);

    $this->postJson(route('panel.customers.draft.commit', $this->customer), ['data' => [], 'rebase' => []])
        ->assertOk();

    expect($this->customer->refresh()->first_name)->toBe('Grace');
});

it('fans a discard out to the slices whose keys the draft held', function () {
    $this->patchJson(route('panel.customers.draft.update', $this->customer), [
        'data' => ['notes:memo' => 'mine'],
    ])->assertOk();

    $draft = EditDraft::sole();

    $this->deleteJson(route('panel.customers.draft.destroy', $this->customer))->assertNoContent();

    expect(MemoSlice::$discarded)->toBe([$draft->id]);
});

it('skips the discard hook for drafts that never held the slice', function () {
    $this->patchJson(route('panel.customers.draft.update', $this->customer), [
        'data' => ['first_name' => 'Grace'],
    ])->assertOk();

    $this->deleteJson(route('panel.customers.draft.destroy', $this->customer))->assertNoContent();

    expect(MemoSlice::$discarded)->toBe([]);
});

it('fans pruning and record deletion out to the slices', function () {
    $this->patchJson(route('panel.customers.draft.update', $this->customer), [
        'data' => ['notes:memo' => 'stale'],
    ])->assertOk();

    $stale = EditDraft::sole();

    $this->travel(8)->days();

    $this->artisan('model:prune', ['--model' => [EditDraft::class]]);

    expect(EditDraft::count())->toBe(0)
        ->and(MemoSlice::$discarded)->toBe([$stale->id]);

    $this->patchJson(route('panel.customers.draft.update', $this->customer), [
        'data' => ['notes:memo' => 'orphaned'],
    ])->assertOk();

    $orphaned = EditDraft::sole();

    $this->customer->delete();

    expect(EditDraft::count())->toBe(0)
        ->and(MemoSlice::$discarded)->toBe([$stale->id, $orphaned->id]);
});

it('seeds edit pages with the slice values of the deepest route-bound record', function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    $channel = Channel::factory()->create();

    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'sku' => 'WID-1']);
    $product->channels()->sync([$channel->id => ['enabled' => true]]);

    $this->get(route('panel.products.edit', $product))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where("draftSliceValues.channel:{$channel->id}.enabled", true)
            ->where('draftSliceValues.variant:sku', 'WID-1'));

    // The variant page drafts the variant, which has no slices: the product's
    // values must not leak in from the parent binding.
    $this->get(route('panel.products.variants.edit', [$product, $variant]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('draftSliceValues', []));

    $this->get(route('panel.customers.edit', $this->customer))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('draftSliceValues.notes:memo', 'hello'));

    $this->get(route('panel.customers.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('draftSliceValues', []));
});
