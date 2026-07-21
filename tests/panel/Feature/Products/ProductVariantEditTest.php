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
        );
});

it('updates the variant through the update endpoint', function () {
    $this->put(route('panel.products.variants.update', [$this->product, $this->variant]), [
        'sku' => 'NEW-SKU',
        'tax_class_id' => $this->variant->tax_class_id,
        'enabled' => false,
        'min_quantity' => 3,
        'selling_policy' => 'in_stock',
        'shippable' => true,
        'unit_quantity' => 1,
        'quantity_increment' => 1,
        'backorder' => 0,
    ])->assertRedirect();

    $this->variant->refresh();

    expect($this->variant->sku)->toBe('NEW-SKU')
        ->and($this->variant->enabled)->toBeFalse()
        ->and($this->variant->min_quantity)->toBe(3);
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
        'data' => [],
        'rebase' => [],
    ])->assertOk();

    $this->variant->refresh();

    expect($this->variant->sku)->toBe('DRAFTED')
        ->and($this->variant->attr('finish'))->toBe('Matte');
});

it('syncs variant media from the product pool in order', function () {
    $this->post(route('panel.products.media.store', $this->product), [
        'files' => [
            UploadedFile::fake()->image('one.jpg', 400, 400),
            UploadedFile::fake()->image('two.jpg', 400, 400),
        ],
    ]);

    [$first, $second] = $this->product->getMedia(config('lunar.media.collection'));

    $this->put(route('panel.products.variants.media.sync', [$this->product, $this->variant]), [
        'ids' => [$second->id, $first->id],
    ])->assertRedirect();

    $images = $this->variant->images()->get();

    expect($images)->toHaveCount(2)
        ->and($images->first()->id)->toBe($second->id)
        ->and((bool) $images->first()->pivot->primary)->toBeTrue();
});

it('rejects media outside the product pool', function () {
    $other = Product::factory()->create();

    $this->post(route('panel.products.media.store', $other), [
        'files' => [UploadedFile::fake()->image('foreign.jpg', 400, 400)],
    ]);

    $foreign = $other->getFirstMedia(config('lunar.media.collection'));

    $this->put(route('panel.products.variants.media.sync', [$this->product, $this->variant]), [
        'ids' => [$foreign->id],
    ])->assertSessionHasErrors('ids.0');
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
