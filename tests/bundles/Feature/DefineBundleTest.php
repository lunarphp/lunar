<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Bundles\Contracts\Actions\DefinesBundle;
use Lunar\Bundles\Contracts\Actions\DeletesBundle;
use Lunar\Bundles\Enums\BundlePricing;
use Lunar\Bundles\Exceptions\BundleNesting;
use Lunar\Bundles\Exceptions\InvalidBundleDefinition;
use Lunar\Bundles\Models\Bundle;
use Lunar\Bundles\Models\BundleComponent;
use Lunar\Bundles\Models\BundleGroup;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Bundles\Support\Catalogue;
use Lunar\Tests\Bundles\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->currency = Catalogue::store();
});

test('a variant can be defined as a bundle and redefined in place', function () {
    $variant = ProductVariant::factory()->create();

    $bundle = app(DefinesBundle::class)->execute($variant, BundlePricing::Fixed);

    expect($bundle)->toBeInstanceOf(Bundle::class)
        ->and($bundle->product_variant_id)->toBe($variant->id)
        ->and($bundle->pricing)->toBe(BundlePricing::Fixed)
        ->and($bundle->discount_percentage)->toBeNull();

    $again = app(DefinesBundle::class)->execute($variant, BundlePricing::Components, 12.5);

    expect($again->id)->toBe($bundle->id)
        ->and($again->pricing)->toBe(BundlePricing::Components)
        ->and($again->discount_percentage)->toBe(12.5)
        ->and(Bundle::count())->toBe(1);
});

test('the variant and product expose the bundle relations', function () {
    $variant = ProductVariant::factory()->create();
    $product = $variant->product;

    expect(ProductVariant::query()->find($variant->id)->bundle)->toBeNull();

    $bundle = app(DefinesBundle::class)->execute($variant, BundlePricing::Fixed);

    expect(ProductVariant::query()->find($variant->id)->bundle->is($bundle))->toBeTrue()
        ->and(Product::query()->find($product->id)->bundles->pluck('id')->all())->toBe([$bundle->id])
        ->and($bundle->variant->is($variant))->toBeTrue();
});

test('a component variant cannot become a bundle', function () {
    $bundle = Bundle::factory()->create();
    $part = ProductVariant::factory()->create();
    BundleComponent::factory()->create(['bundle_id' => $bundle->id, 'product_variant_id' => $part->id]);

    app(DefinesBundle::class)->execute($part, BundlePricing::Fixed);
})->throws(BundleNesting::class);

test('a bundle cannot contain itself or another bundle', function () {
    $bundle = Bundle::factory()->create();
    $other = Bundle::factory()->create();

    expect(fn () => $bundle->syncComponents([['variant' => $bundle->product_variant_id, 'quantity' => 1]]))
        ->toThrow(BundleNesting::class);

    expect(fn () => $bundle->syncComponents([['variant' => $other->variant, 'quantity' => 1]]))
        ->toThrow(BundleNesting::class);
});

test('a bundle needs at least one and at most max_components components', function () {
    config()->set('lunar.bundles.max_components', 2);
    $bundle = Bundle::factory()->create();
    $parts = ProductVariant::factory()->count(3)->create();

    expect(fn () => $bundle->syncComponents([]))->toThrow(InvalidBundleDefinition::class);

    expect(fn () => $bundle->syncComponents(
        $parts->map(fn ($part) => ['variant' => $part, 'quantity' => 1])->all()
    ))->toThrow(InvalidBundleDefinition::class);

    $bundle->syncComponents($parts->take(2)->map(fn ($part) => ['variant' => $part, 'quantity' => 1])->all());

    expect($bundle->components)->toHaveCount(2);
});

test('components sync upserts, reorders and removes to match the input', function () {
    $bundle = Bundle::factory()->create();
    [$a, $b, $c] = ProductVariant::factory()->count(3)->create();

    $bundle->syncComponents([
        ['variant' => $a, 'quantity' => 1],
        ['variant' => $b, 'quantity' => 2],
    ]);

    $first = $bundle->components->firstWhere('product_variant_id', $a->id);

    $bundle->syncComponents([
        ['variant' => $c, 'quantity' => 1, 'position' => 0],
        ['variant' => $a->id, 'quantity' => 3, 'position' => 1],
    ]);

    $components = $bundle->fresh()->components;

    expect($components->pluck('product_variant_id')->all())->toBe([$c->id, $a->id])
        ->and($components->firstWhere('product_variant_id', $a->id)->id)->toBe($first->id)
        ->and($components->firstWhere('product_variant_id', $a->id)->quantity)->toBe(3)
        ->and(BundleComponent::where('product_variant_id', $b->id)->exists())->toBeFalse();
});

test('groups sync creates, updates and deletes groups with their components', function () {
    $bundle = Bundle::factory()->create();
    [$a, $b] = ProductVariant::factory()->count(2)->create();

    $bundle->syncGroups([
        ['name' => ['en' => 'Lens'], 'min_selections' => 1, 'max_selections' => 1],
        ['name' => ['en' => 'Bag'], 'min_selections' => 0, 'max_selections' => 1],
    ]);

    [$lens, $bag] = $bundle->groups;

    $bundle->syncComponents([
        ['variant' => $a, 'quantity' => 1, 'group' => $lens, 'default' => true],
        ['variant' => $b, 'quantity' => 1, 'group' => $bag->id],
    ]);

    expect($bundle->isConfigurable())->toBeTrue()
        ->and($bundle->components->firstWhere('product_variant_id', $a->id)->group->is($lens))->toBeTrue();

    $bundle->syncGroups([
        ['id' => $lens->id, 'name' => ['en' => 'Lens choice'], 'min_selections' => 1, 'max_selections' => 1, 'position' => 5],
    ]);

    expect($bundle->groups->pluck('id')->all())->toBe([$lens->id])
        ->and($bundle->groups->first()->translate('name'))->toBe('Lens choice')
        ->and($bundle->groups->first()->position)->toBe(5)
        ->and(BundleGroup::find($bag->id))->toBeNull()
        ->and(BundleComponent::where('product_variant_id', $b->id)->exists())->toBeFalse()
        ->and(BundleComponent::where('product_variant_id', $a->id)->exists())->toBeTrue();
});

test('group selection limits are validated against each other and the group size', function () {
    $bundle = Bundle::factory()->create();
    $group = BundleGroup::factory()->create(['bundle_id' => $bundle->id, 'min_selections' => 1, 'max_selections' => 1]);
    $part = ProductVariant::factory()->create();
    $stranger = BundleGroup::factory()->create();

    expect(fn () => $bundle->syncGroups([
        ['id' => $group->id, 'name' => ['en' => 'Lens'], 'min_selections' => 2, 'max_selections' => 1],
    ]))->toThrow(InvalidBundleDefinition::class);

    expect(fn () => $bundle->syncComponents([
        ['variant' => $part, 'quantity' => 1, 'group' => $stranger],
    ]))->toThrow(InvalidBundleDefinition::class);

    $bundle->syncComponents([['variant' => $part, 'quantity' => 1, 'group' => $group]]);

    expect(fn () => $bundle->syncGroups([
        ['id' => $group->id, 'name' => ['en' => 'Lens'], 'min_selections' => 1, 'max_selections' => 2],
    ]))->toThrow(InvalidBundleDefinition::class);
});

test('deleting a bundle removes its definition and leaves the variant and prices alone', function () {
    $variant = Catalogue::variant($this->currency, 1000);
    $bundle = app(DefinesBundle::class)->execute($variant, BundlePricing::Fixed);
    $group = BundleGroup::factory()->create(['bundle_id' => $bundle->id]);
    BundleComponent::factory()->create(['bundle_id' => $bundle->id, 'bundle_group_id' => $group->id]);

    app(DeletesBundle::class)->execute($bundle);

    expect(Bundle::find($bundle->id))->toBeNull()
        ->and(BundleGroup::where('bundle_id', $bundle->id)->exists())->toBeFalse()
        ->and(BundleComponent::where('bundle_id', $bundle->id)->exists())->toBeFalse()
        ->and(ProductVariant::find($variant->id))->not->toBeNull()
        ->and($variant->prices()->count())->toBe(1);
});
