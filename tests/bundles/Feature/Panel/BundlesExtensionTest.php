<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Bundles\Models\Bundle;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Bundles\PanelTestCase;

uses(PanelTestCase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
});

it('registers the bundle cards in the product page zones', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

    $this->get(route('panel.products.edit', $product))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('slots', function ($slots) {
                $variants = collect($slots->get('products.edit:variants:after', []))->pluck('component');
                $sidebar = collect($slots->get('products.edit:sidebar:after', []))->pluck('component');

                return $variants->contains('bundles::BundleCard')
                    && $sidebar->contains('bundles::IncludedInBundlesCard');
            })
        );

    $this->get(route('panel.products.variants.edit', [$product, $variant]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('slots', fn ($slots) => collect($slots->get('products.variants.edit:main:after', []))
                ->pluck('component')
                ->contains('bundles::BundleCard'))
        );
});

it('hides the bundle slots from staff without the products permission', function () {
    $this->actingAs(Staff::factory()->create(['admin' => false]), 'staff');

    $this->get(route('panel.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('slots', fn ($slots) => collect($slots->all())
                ->flatten(1)
                ->pluck('component')
                ->filter(fn ($component) => str_starts_with($component, 'bundles::'))
                ->isEmpty())
        );
});

it('serves the bundles lang namespace to the panel frontend', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $this->get(route('panel.translations', ['locale' => 'en']))
        ->assertOk()
        ->assertJsonPath('messages.bundles::bundles.panel.card_title', 'Bundle');
});

it('adds a bundle badge column and a bundles-only filter to the products table', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $bundle = Bundle::factory()->create();
    $bundleProduct = $bundle->variant->product;
    $plain = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $plain->id]);

    $this->get(route('panel.products.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('columns', fn ($columns) => collect($columns)->firstWhere('key', 'is_bundle')['component'] === 'bundles::BundleBadge')
            ->where('tableFilters.0.key', 'bundles')
            ->has('products.data', 2)
            ->where('products.data', fn ($rows) => collect($rows)
                ->mapWithKeys(fn ($row) => [$row['id'] => (bool) $row['is_bundle']])
                ->all() === [$bundleProduct->id => true, $plain->id => false])
        );

    $this->get(route('panel.products.index', ['filter' => ['bundles' => 'only']]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.id', $bundleProduct->id)
        );
});
