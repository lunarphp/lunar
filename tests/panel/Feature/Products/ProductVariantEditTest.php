<?php

use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Models\EditDraft;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Language::factory()->create(['default' => true, 'code' => 'en']);

    $this->product = Product::factory()->create(['name' => collect(['en' => 'Widget'])]);
    [$this->variant, $this->sibling] = ProductVariant::factory()->count(2)
        ->create(['product_id' => $this->product->id])
        ->sortBy('id')->values();
});

it('renders the variant edit page with navigation and payloads', function () {
    $this->get(route('panel.products.variants.edit', [$this->product, $this->variant]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('products/VariantEdit')
            ->where('product.name', 'Widget')
            ->where('variant.id', $this->variant->id)
            ->where('variant.position', 1)
            ->where('variant.total', 2)
            ->where('variant.prev_url', null)
            ->where('variant.next_url', route('panel.products.variants.edit', [$this->product, $this->sibling]))
            ->has('variantValues')
            ->has('variant.stock.levels')
            ->has('mediaPool')
            ->has('urls.draft')
            ->where('canDelete', true)
            ->where('deleteBlockedReason', null)
        );
});

it('explains why a variant cannot be deleted', function () {
    // Last remaining variant.
    $this->sibling->forceDelete();

    $this->get(route('panel.products.variants.edit', [$this->product, $this->variant]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('canDelete', false)
            ->where('deleteBlockedReason', 'last_variant')
        );

    // Order history takes precedence once a sibling exists again.
    ProductVariant::factory()->create(['product_id' => $this->product->id]);
    OrderLine::factory()->create([
        'purchasable_type' => $this->variant->getMorphClass(),
        'purchasable_id' => $this->variant->id,
    ]);

    $this->get(route('panel.products.variants.edit', [$this->product, $this->variant]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('canDelete', false)
            ->where('deleteBlockedReason', 'order_history')
        );
});

it('lists every registered fulfilment method, physical ones first', function () {
    $this->variant->update(['fulfilment_methods' => ['pickup']]);

    $this->get(route('panel.products.variants.edit', [$this->product, $this->variant]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('variantValues.fulfilment_methods', ['pickup'])
            ->where('fulfilmentMethods', fn ($methods) => collect($methods)->map(fn ($method) => [$method['key'], $method['physical']])->all() === [
                ['pickup', true],
                ['shipping', true],
                ['digital', false],
            ])
        );
});

it('commits the allowed fulfilment methods, an empty selection as null', function () {
    $commit = fn (array $methods) => $this->postJson(route('panel.products.variants.draft.commit', [$this->product, $this->variant]), [
        'data' => ['fulfilment_methods' => $methods],
        'rebase' => [],
    ])->assertOk();

    $commit(['shipping', 'pickup']);

    expect($this->variant->refresh()->fulfilment_methods)->toBe(['pickup', 'shipping']);

    $commit([]);

    expect($this->variant->refresh()->fulfilment_methods)->toBeNull();
});

it('rejects an unregistered fulfilment method', function () {
    $this->postJson(route('panel.products.variants.draft.commit', [$this->product, $this->variant]), [
        'data' => ['fulfilment_methods' => ['teleport']],
        'rebase' => [],
    ])->assertJsonValidationErrors('fulfilment_methods');
});

it('rejects physical and non-physical fulfilment methods together', function () {
    $this->postJson(route('panel.products.variants.draft.commit', [$this->product, $this->variant]), [
        'data' => ['fulfilment_methods' => ['pickup', 'digital']],
        'rebase' => [],
    ])->assertJsonValidationErrors(['fulfilment_methods' => __('panel::products.field_fulfilment_methods_mixed')]);
});

it('drafts and commits variant fields including attribute values', function () {
    $attribute = Attribute::factory()->modelType('product_variant')->create([
        'handle' => 'finish',
        'type' => 'text',
    ]);

    $this->product->productType->attributeMapping()->sync([$attribute->id]);

    $this->patchJson(route('panel.products.variants.draft.update', [$this->product, $this->variant]), [
        'data' => ['sku' => 'DRAFTED', 'attribute:finish' => 'Matte'],
    ])->assertOk();

    expect(EditDraft::sole()->data['sku'])->toBe('DRAFTED');

    $this->postJson(route('panel.products.variants.draft.commit', [$this->product, $this->variant]), [
        'data' => ['sku' => 'DRAFTED', 'attribute:finish' => 'Matte'],
        'rebase' => [],
    ])->assertOk();

    $this->variant->refresh();

    expect($this->variant->sku)->toBe('DRAFTED')
        ->and($this->variant->attr('finish'))->toBe('Matte');
});

it('drafts and commits the variant image selection from the product pool', function () {
    [$first, $second] = collect(['one.jpg', 'two.jpg'])
        ->map(fn ($name) => $this->product->addMedia(UploadedFile::fake()->image($name, 400, 400))->toMediaCollection(config('lunar.media.collection')))
        ->all();

    $this->get(route('panel.products.variants.edit', [$this->product, $this->variant]))
        ->assertInertia(fn (Assert $page) => $page->where('formSliceValues.variant_media:ids', []));

    $data = ['variant_media:ids' => [$second->id, $first->id]];

    $this->patchJson(route('panel.products.variants.draft.update', [$this->product, $this->variant]), ['data' => $data])
        ->assertOk();

    expect($this->variant->images()->count())->toBe(0);

    $this->postJson(route('panel.products.variants.draft.commit', [$this->product, $this->variant]), ['data' => $data, 'rebase' => []])
        ->assertOk();

    $images = $this->variant->images()->orderBy('position')->get();

    expect($images->modelKeys())->toBe([$second->id, $first->id])
        ->and((bool) $images->first()->pivot->primary)->toBeTrue();
});

it('refuses variant images outside the product pool', function () {
    $other = Product::factory()->create();
    $foreign = $other->addMedia(UploadedFile::fake()->image('foreign.jpg', 400, 400))->toMediaCollection(config('lunar.media.collection'));

    $this->postJson(route('panel.products.variants.draft.commit', [$this->product, $this->variant]), [
        'data' => ['variant_media:ids' => [$foreign->id]],
        'rebase' => [],
    ])->assertJsonValidationErrors('variant_media:ids.0');
});

it('deletes a variant with siblings and refuses guarded ones', function () {
    $this->delete(route('panel.products.variants.destroy', [$this->product, $this->variant]))
        ->assertRedirect(route('panel.products.edit', $this->product));

    $this->assertDatabaseMissing('lunar_product_variants', ['id' => $this->variant->id]);

    // The survivor is now the last variant — refused.
    $this->delete(route('panel.products.variants.destroy', [$this->product, $this->sibling]))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('lunar_product_variants', ['id' => $this->sibling->id]);
});

it('refuses deleting a variant with order history', function () {
    OrderLine::factory()->create([
        'purchasable_type' => $this->variant->getMorphClass(),
        'purchasable_id' => $this->variant->id,
    ]);

    $this->delete(route('panel.products.variants.destroy', [$this->product, $this->variant]))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('lunar_product_variants', ['id' => $this->variant->id]);
});

it('scopes the variant edit page to the owning product', function () {
    $foreign = ProductVariant::factory()->create();

    $this->get(route('panel.products.variants.edit', [$this->product, $foreign]))
        ->assertNotFound();
});
