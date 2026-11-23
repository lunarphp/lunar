<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Enums\ProductAssociation as ProductAssociationEnum;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductAssociation;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true, 'decimal_places' => 2]);

    $this->product = Product::factory()->create();
    [$this->a, $this->b, $this->c] = Product::factory()->count(3)->create()->all();
});

/** @return array<int, int> */
function syncedTargets(Product $product, string $type): array
{
    return $product->associations()->where('type', $type)->pluck('product_target_id')->map(fn ($id) => (int) $id)->all();
}

test('links the targets in the given order', function () {
    $this->product->syncAssociations('cross-sell', [$this->c->id, $this->a->id]);

    expect(syncedTargets($this->product, 'cross-sell'))->toBe([$this->c->id, $this->a->id])
        ->and($this->product->associations()->where('type', 'cross-sell')->pluck('sort')->all())->toBe([1, 2]);
});

test('keeps the rows of targets still listed, reorders them and deletes the rest', function () {
    $this->product->syncAssociations('cross-sell', [$this->a->id, $this->b->id, $this->c->id]);
    $rowForC = $this->product->associations()->where('product_target_id', $this->c->id)->value('id');

    $this->product->syncAssociations('cross-sell', [$this->c->id, $this->a->id]);

    expect(syncedTargets($this->product, 'cross-sell'))->toBe([$this->c->id, $this->a->id])
        ->and($this->product->associations()->where('product_target_id', $this->c->id)->value('id'))->toBe($rowForC);
});

test('drops duplicate ids, the product itself and ids with no product', function () {
    $this->product->syncAssociations('up-sell', [$this->b->id, $this->product->id, $this->b->id, 999999, $this->a->id]);

    expect(syncedTargets($this->product, 'up-sell'))->toBe([$this->b->id, $this->a->id]);
});

test('collapses duplicate rows left by earlier queued associations', function () {
    foreach ([1, 2] as $sort) {
        ProductAssociation::factory()->create([
            'product_parent_id' => $this->product->id,
            'product_target_id' => $this->a->id,
            'type' => 'alternate',
            'sort' => $sort,
        ]);
    }

    $this->product->syncAssociations(ProductAssociationEnum::ALTERNATE, [$this->a->id]);

    expect(syncedTargets($this->product, 'alternate'))->toBe([$this->a->id]);
});

test('leaves other association types untouched', function () {
    $this->product->syncAssociations('up-sell', [$this->a->id]);

    $this->product->syncAssociations('cross-sell', [$this->b->id]);
    $this->product->syncAssociations('cross-sell', []);

    expect(syncedTargets($this->product, 'up-sell'))->toBe([$this->a->id])
        ->and(syncedTargets($this->product, 'cross-sell'))->toBe([]);
});

test('associating a target already linked does not link it twice', function () {
    $this->product->associate($this->a, 'cross-sell');
    $this->product->associate([$this->a, $this->b], 'cross-sell');

    expect(syncedTargets($this->product, 'cross-sell'))->toBe([$this->a->id, $this->b->id]);
});

test('dissociating without a type removes the target from every type', function () {
    $this->product->syncAssociations('cross-sell', [$this->a->id, $this->b->id]);
    $this->product->syncAssociations('up-sell', [$this->a->id]);

    $this->product->dissociate($this->a);

    expect(syncedTargets($this->product, 'cross-sell'))->toBe([$this->b->id])
        ->and(syncedTargets($this->product, 'up-sell'))->toBe([]);
});
