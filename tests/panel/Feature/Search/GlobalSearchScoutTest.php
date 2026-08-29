<?php

use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductType;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Facades\Panel;
use Lunar\Tests\Panel\Fixtures\ScoutSearchTestCase;
use Lunar\Tests\Panel\Fixtures\Search\ProductTypeSearchSource;

uses(ScoutSearchTestCase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true]);
    Currency::factory()->create(['default' => true]);
});

it('matches indexed models through scout when the store has opted in', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    $product = Product::factory()->create(['name' => collect(['en' => 'Scouted Lamp'])]);

    $rows = collect(
        $this->actingAs($staff, 'staff')->getJson('/panel/search?q=Scouted')->assertOk()->json('data')
    );

    expect($rows->firstWhere('kind', 'products')['id'])->toBe($product->id);
});

it('still caps each source when matching through scout', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    Product::factory()->count(8)->sequence(fn ($sequence) => [
        'name' => collect(['en' => 'Capped Lamp '.$sequence->index]),
    ])->create();

    $rows = collect(
        $this->actingAs($staff, 'staff')->getJson('/panel/search?q=Capped')->assertOk()->json('data')
    );

    expect($rows->where('kind', 'products'))->toHaveCount(5);
});

it('falls back to LIKE for a source whose model is not indexed', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    // ProductType carries no Scout index, so the flag must not stop it
    // matching alongside the indexed sources.
    Panel::searchSource(ProductTypeSearchSource::class);

    $productType = ProductType::factory()->create(['name' => 'Mixed Setup Type']);
    $product = Product::factory()->create(['name' => collect(['en' => 'Mixed Setup Lamp'])]);

    $rows = collect(
        $this->actingAs($staff, 'staff')->getJson('/panel/search?q=Mixed Setup')->assertOk()->json('data')
    );

    expect($rows->firstWhere('kind', 'product-types')['id'])->toBe($productType->id)
        ->and($rows->firstWhere('kind', 'products')['id'])->toBe($product->id);
});
