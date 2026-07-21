<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');

    Language::factory()->create(['default' => true, 'code' => 'en']);

    $this->product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $this->product->id]);
});

it('links picked products with the requested type', function () {
    $targets = Product::factory()->count(2)->create();

    $this->post(route('panel.products.associations.store', $this->product), [
        'type' => 'cross-sell',
        'product_ids' => $targets->pluck('id')->all(),
    ])->assertRedirect();

    expect($this->product->associations()->where('type', 'cross-sell')->count())->toBe(2);
});

it('skips already-linked targets and the product itself', function () {
    $target = Product::factory()->create();
    $this->product->associations()->create(['product_target_id' => $target->id, 'type' => 'up-sell']);

    $this->post(route('panel.products.associations.store', $this->product), [
        'type' => 'up-sell',
        'product_ids' => [$target->id, $this->product->id],
    ])->assertRedirect();

    expect($this->product->associations()->where('type', 'up-sell')->count())->toBe(1);
});

it('rejects unknown association types', function () {
    $target = Product::factory()->create();

    $this->post(route('panel.products.associations.store', $this->product), [
        'type' => 'bff',
        'product_ids' => [$target->id],
    ])->assertSessionHasErrors('type');
});

it('removes a link and serves the grouped payload on the edit page', function () {
    $target = Product::factory()->create(['name' => collect(['en' => 'Charger'])]);
    $association = $this->product->associations()->create([
        'product_target_id' => $target->id,
        'type' => 'cross-sell',
    ]);

    $this->get(route('panel.products.edit', $this->product))
        ->assertInertia(fn (Assert $page) => $page
            ->where('associations.cross-sell.0.name', 'Charger')
            ->has('associations.alternate', 0)
        );

    $this->delete(route('panel.products.associations.destroy', [$this->product, $association]))
        ->assertRedirect();

    expect($this->product->associations()->count())->toBe(0);
});

it('scopes association routes to the owning product', function () {
    $target = Product::factory()->create();
    $other = Product::factory()->create();
    $association = $other->associations()->create([
        'product_target_id' => $target->id,
        'type' => 'cross-sell',
    ]);

    $this->delete(route('panel.products.associations.destroy', [$this->product, $association]))
        ->assertNotFound();
});
