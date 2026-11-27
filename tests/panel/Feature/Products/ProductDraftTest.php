<?php

use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductType;
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
        'data' => [
            'name' => ['en' => 'Renamed Widget'],
            'status' => 'published',
            'tags' => ['SALE'],
        ],
        'rebase' => [],
    ])->assertOk();

    $this->product->refresh();

    expect($this->product->translate('name'))->toBe('Renamed Widget')
        ->and((string) $this->product->status)->toBe('published')
        ->and($this->product->tags->pluck('value')->all())->toBe(['SALE'])
        ->and(EditDraft::count())->toBe(0);
});

it('saves the product once when only its own fields changed', function () {
    $attribute = Attribute::factory()->modelType('product')->create(['handle' => 'material', 'type' => 'text']);
    $this->product->productType->attributeMapping()->sync([$attribute->id]);
    Channel::factory()->create();
    CustomerGroup::factory()->create();

    $saves = 0;
    Product::saved(function () use (&$saves): void {
        $saves++;
    });

    $this->patchJson(route('panel.products.draft.update', $this->product), [
        'data' => ['name' => ['en' => 'Renamed Widget']],
    ])->assertOk();

    $this->postJson(route('panel.products.draft.commit', $this->product), [
        'data' => ['name' => ['en' => 'Renamed Widget']],
        'rebase' => [],
    ])
        ->assertOk();

    expect($saves)->toBe(1);
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
        'data' => ['attribute:material' => 'Titanium'],
        'rebase' => [],
    ])->assertOk();

    expect($this->product->refresh()->attr('material'))->toBe('Titanium');
});

it('validates and saves attributes against the product type the commit selects', function () {
    $shared = Attribute::factory()->modelType('product')->create(['handle' => 'material', 'type' => 'text']);
    $finish = Attribute::factory()->modelType('product')->create(['handle' => 'finish', 'type' => 'text', 'required' => true]);
    $voltage = Attribute::factory()->modelType('product')->create(['handle' => 'voltage', 'type' => 'text', 'required' => true]);
    $this->product->productType->attributeMapping()->sync([$shared->id, $finish->id]);
    $this->product->update(['attribute_data' => ['material' => 'Steel', 'finish' => 'Matte']]);

    $electrical = ProductType::factory()->create();
    $electrical->attributeMapping()->sync([$shared->id, $voltage->id]);

    // The new type's required attribute is enforced, and the old type's
    // required attribute no longer is.
    $this->postJson(route('panel.products.draft.commit', $this->product), [
        'data' => ['product_type_id' => $electrical->id, 'attribute:finish' => ''],
        'rebase' => [],
    ])
        ->assertJsonValidationErrors('attribute:voltage')
        ->assertJsonMissingValidationErrors('attribute:finish');

    $this->postJson(route('panel.products.draft.commit', $this->product), [
        'data' => [
            'product_type_id' => $electrical->id,
            'attribute:material' => 'Brass',
            'attribute:voltage' => '240V',
            'attribute:finish' => 'Gloss',
        ],
        'rebase' => [],
    ])->assertOk();

    $this->product->refresh();

    // A value for an attribute the new type does not carry is not written.
    expect($this->product->product_type_id)->toBe($electrical->id)
        ->and($this->product->attr('material'))->toBe('Brass')
        ->and($this->product->attr('voltage'))->toBe('240V')
        ->and($this->product->attr('finish'))->toBe('Matte');
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
        'data' => [
            "customer_group:{$group->id}" => [
                'enabled' => true,
                'visible' => true,
                'purchasable' => false,
                'starts_at' => null,
                'ends_at' => null,
            ],
        ],
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
            'variant:continue_selling' => false,
            'variant:sell_against_incoming' => true,
        ],
    ])->assertOk();

    $this->postJson(route('panel.products.draft.commit', $this->product), [
        'data' => [
            'variant:sku' => 'WID-NEW',
            'variant:shippable' => false,
            'variant:min_quantity' => 4,
            'variant:continue_selling' => false,
            'variant:sell_against_incoming' => true,
        ],
        'rebase' => [],
    ])->assertOk();

    $variant->refresh();

    expect($variant->sku)->toBe('WID-NEW')
        ->and($variant->shippable)->toBeFalse()
        ->and($variant->min_quantity)->toBe(4)
        ->and($variant->continue_selling)->toBeFalse()
        ->and($variant->sell_against_incoming)->toBeTrue();
});

it('drafts and commits the sole variant allowed fulfilment methods', function () {
    $variant = $this->product->variants()->sole();

    $this->patchJson(route('panel.products.draft.update', $this->product), [
        'data' => ['variant:fulfilment_methods' => ['pickup']],
    ])->assertOk();

    $this->postJson(route('panel.products.draft.commit', $this->product), [
        'data' => ['variant:fulfilment_methods' => ['pickup']],
        'rebase' => [],
    ])->assertOk();

    expect($variant->refresh()->fulfilment_methods)->toBe(['pickup']);
});

it('refuses variant fields while the product has several variants', function () {
    ProductVariant::factory()->create(['product_id' => $this->product->id]);

    $this->patchJson(route('panel.products.draft.update', $this->product), [
        'data' => ['variant:sku' => 'NOPE'],
    ])->assertOk();

    $this->postJson(route('panel.products.draft.commit', $this->product), [
        'data' => ['variant:sku' => 'NOPE'],
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
        'data' => ['name' => ['en' => 'Mine']],
        'rebase' => [],
    ])->assertConflict();
});
