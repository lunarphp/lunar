<?php

use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Models\EditDraft;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');

    Language::factory()->create(['default' => true, 'code' => 'en']);

    $this->product = Product::factory()->create(['name' => collect(['en' => 'Widget'])]);
    ProductVariant::factory()->create(['product_id' => $this->product->id]);
});

it('autosaves changed fields to a draft', function () {
    $this->patchJson(route('panel.products.draft.update', $this->product), [
        'data' => ['name' => ['en' => 'Renamed Widget']],
    ])->assertOk();

    $draft = EditDraft::sole();

    expect($draft->data)->toBe(['name' => ['en' => 'Renamed Widget']])
        ->and($draft->base_snapshot['name'])->toBe(['en' => 'Widget']);
});

it('normalises tags and collection ids when drafting', function () {
    $group = CollectionGroup::factory()->create();
    $collections = Collection::factory()->count(2)->create(['collection_group_id' => $group->id]);
    [$first, $second] = $collections->sortBy('id')->values();

    $this->patchJson(route('panel.products.draft.update', $this->product), [
        'data' => [
            'tags' => ['sale', 'Festive', 'SALE'],
            'collection_ids' => [$second->id, $first->id, $first->id],
        ],
    ])->assertOk();

    $draft = EditDraft::sole();

    expect($draft->data['tags'])->toBe(['FESTIVE', 'SALE'])
        ->and($draft->data['collection_ids'])->toBe([$first->id, $second->id]);
});

it('rejects fields outside the draftable set', function () {
    $this->patchJson(route('panel.products.draft.update', $this->product), [
        'data' => ['public_id' => 'nope'],
    ])->assertUnprocessable();
});

it('commits a draft through the update action', function () {
    $this->patchJson(route('panel.products.draft.update', $this->product), [
        'data' => [
            'name' => ['en' => 'Renamed Widget'],
            'status' => 'published',
            'tags' => ['SALE'],
        ],
    ])->assertOk();

    $this->postJson(route('panel.products.draft.commit', $this->product), [
        'data' => [],
        'rebase' => [],
    ])->assertOk();

    $this->product->refresh();

    expect($this->product->translate('name'))->toBe('Renamed Widget')
        ->and((string) $this->product->status)->toBe('published')
        ->and($this->product->tags->pluck('value')->all())->toBe(['SALE'])
        ->and(EditDraft::count())->toBe(0);
});

it('commits product attribute values into attribute data', function () {
    $attribute = Attribute::factory()->modelType('product')->create([
        'handle' => 'material',
        'type' => 'text',
    ]);

    $this->product->productType->attributeMapping()->sync([$attribute->id]);

    $this->patchJson(route('panel.products.draft.update', $this->product), [
        'data' => ['attribute:material' => 'Titanium'],
    ])->assertOk();

    $this->postJson(route('panel.products.draft.commit', $this->product), [
        'data' => [],
        'rebase' => [],
    ])->assertOk();

    expect($this->product->refresh()->attr('material'))->toBe('Titanium');
});

it('commits drafted availability rows including the purchasable flag', function () {
    $group = CustomerGroup::factory()->create();

    $this->patchJson(route('panel.products.draft.update', $this->product), [
        'data' => [
            "customer_group:{$group->id}" => [
                'enabled' => true,
                'visible' => true,
                'purchasable' => false,
                'starts_at' => null,
                'ends_at' => null,
            ],
        ],
    ])->assertOk();

    $this->postJson(route('panel.products.draft.commit', $this->product), [
        'data' => [],
        'rebase' => [],
    ])->assertOk();

    $pivot = $this->product->refresh()->customerGroups->firstWhere('id', $group->id)->pivot;

    expect((bool) $pivot->enabled)->toBeTrue()
        ->and((bool) $pivot->purchasable)->toBeFalse();
});

it('commits simple-shape variant fields through the variant action', function () {
    $variant = $this->product->variants()->sole();

    $this->patchJson(route('panel.products.draft.update', $this->product), [
        'data' => [
            'variant:sku' => 'WID-NEW',
            'variant:shippable' => false,
            'variant:min_quantity' => 4,
            'variant:selling_policy' => 'in_stock',
        ],
    ])->assertOk();

    $this->postJson(route('panel.products.draft.commit', $this->product), [
        'data' => [],
        'rebase' => [],
    ])->assertOk();

    $variant->refresh();

    expect($variant->sku)->toBe('WID-NEW')
        ->and($variant->shippable)->toBeFalse()
        ->and($variant->min_quantity)->toBe(4)
        ->and($variant->selling_policy->value)->toBe('in_stock');
});

it('refuses variant fields while the product has several variants', function () {
    ProductVariant::factory()->create(['product_id' => $this->product->id]);

    $this->patchJson(route('panel.products.draft.update', $this->product), [
        'data' => ['variant:sku' => 'NOPE'],
    ])->assertOk();

    $this->postJson(route('panel.products.draft.commit', $this->product), [
        'data' => [],
        'rebase' => [],
    ])->assertUnprocessable();
});

it('detects a field conflict on commit', function () {
    $this->patchJson(route('panel.products.draft.update', $this->product), [
        'data' => ['name' => ['en' => 'Mine']],
    ])->assertOk();

    // Another staff member edits the same field directly in the meantime.
    $this->product->update(['name' => collect(['en' => 'Theirs'])]);

    $this->postJson(route('panel.products.draft.commit', $this->product), [
        'data' => [],
        'rebase' => [],
    ])->assertConflict();
});
