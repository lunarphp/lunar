<?php

use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\ProductType;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Models\EditDraft;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');

    $this->productType = ProductType::factory()->create([
        'name' => 'Stationery',
        'handle' => 'stationery',
    ]);
});

it('autosaves changed fields to a draft', function () {
    $this->patchJson(route('panel.product-types.draft.update', $this->productType), [
        'data' => ['name' => 'Office Supplies'],
    ])->assertOk();

    $draft = EditDraft::sole();

    expect($draft->data)->toBe(['name' => 'Office Supplies'])
        ->and($draft->base_snapshot)->toHaveKey('name', 'Stationery');
});

it('normalises attribute id sets when drafting', function () {
    $attributes = Attribute::factory()->modelType('product')->count(2)->create();
    [$first, $second] = $attributes->sortBy('id')->values();

    $this->patchJson(route('panel.product-types.draft.update', $this->productType), [
        'data' => ['product_attribute_ids' => [$second->id, $first->id, $first->id]],
    ])->assertOk();

    expect(EditDraft::sole()->data['product_attribute_ids'])->toBe([$first->id, $second->id]);
});

it('rejects fields outside the draftable set', function () {
    $this->patchJson(route('panel.product-types.draft.update', $this->productType), [
        'data' => ['public_id' => 'nope'],
    ])->assertUnprocessable();
});

it('commits a draft through the update action', function () {
    $this->patchJson(route('panel.product-types.draft.update', $this->productType), [
        'data' => ['name' => 'Office Supplies', 'status' => 'draft'],
    ]);

    $this->postJson(route('panel.product-types.draft.commit', $this->productType), [
        'data' => [],
        'rebase' => [],
    ])->assertOk();

    $this->productType->refresh();

    expect($this->productType->name)->toBe('Office Supplies')
        ->and($this->productType->status->getValue())->toBe('draft')
        ->and(EditDraft::count())->toBe(0);
});

it('commits one mapping surface without clearing the other', function () {
    $productAttribute = Attribute::factory()->modelType('product')->create();
    $variantAttribute = Attribute::factory()->modelType('product_variant')->create();

    $this->productType->attributeMapping()->sync([$variantAttribute->id]);

    $this->patchJson(route('panel.product-types.draft.update', $this->productType), [
        'data' => ['product_attribute_ids' => [$productAttribute->id]],
    ])->assertOk();

    $this->postJson(route('panel.product-types.draft.commit', $this->productType), [
        'data' => [],
        'rebase' => [],
    ])->assertOk();

    expect($this->productType->productAttributes()->get()->modelKeys())->toBe([$productAttribute->id])
        ->and($this->productType->variantAttributes()->get()->modelKeys())->toBe([$variantAttribute->id]);
});

it('commits type-level attribute values into attribute data', function () {
    Attribute::factory()->modelType('product_type')->create([
        'handle' => 'buying_guide',
        'type' => 'text',
    ]);

    $this->patchJson(route('panel.product-types.draft.update', $this->productType), [
        'data' => ['attribute:buying_guide' => 'How to choose the right pen.'],
    ])->assertOk();

    $this->postJson(route('panel.product-types.draft.commit', $this->productType), [
        'data' => [],
        'rebase' => [],
    ])->assertOk();

    expect($this->productType->refresh()->attr('buying_guide'))->toBe('How to choose the right pen.');
});

it('rejects an invalid merged payload at commit', function () {
    ProductType::factory()->create(['handle' => 'taken']);

    $this->patchJson(route('panel.product-types.draft.update', $this->productType), [
        'data' => ['handle' => 'taken'],
    ])->assertOk();

    $this->postJson(route('panel.product-types.draft.commit', $this->productType), [
        'data' => [],
        'rebase' => [],
    ])->assertUnprocessable();
});

it('detects a conflict when the same field changed underneath the draft', function () {
    $this->patchJson(route('panel.product-types.draft.update', $this->productType), [
        'data' => ['name' => 'Mine'],
    ]);

    $this->productType->update(['name' => 'Theirs']);

    $response = $this->postJson(route('panel.product-types.draft.commit', $this->productType), [
        'data' => [],
        'rebase' => [],
    ]);

    $response->assertConflict()
        ->assertJsonPath('conflicts.0.key', 'name')
        ->assertJsonPath('conflicts.0.mine', 'Mine')
        ->assertJsonPath('conflicts.0.theirs', 'Theirs');
});
