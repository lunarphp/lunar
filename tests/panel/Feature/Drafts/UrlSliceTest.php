<?php

use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Staff;
use Lunar\Core\Models\Url;
use Lunar\Panel\Models\EditDraft;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    // Each test owns its URL rows.
    config(['lunar.urls.generator' => null]);

    $this->en = Language::factory()->create(['default' => true, 'code' => 'en']);
    $this->de = Language::factory()->create(['default' => false, 'code' => 'de']);
});

dataset('url records', [
    'product' => [fn () => tap(Product::factory()->create(), fn (Product $product) => ProductVariant::factory()->create(['product_id' => $product->id])), 'products'],
    'brand' => [fn () => Brand::factory()->create(), 'brands'],
    'collection' => [fn () => Collection::factory()->create(['collection_group_id' => CollectionGroup::factory()]), 'collections'],
]);

/** @return array<int, array{slug: string, default: bool}> */
function slugRows($record, Language $language): array
{
    return $record->urls()->where('language_id', $language->id)->orderBy('id')->get()
        ->map(fn (Url $url) => ['slug' => $url->slug, 'default' => (bool) $url->default])->all();
}

function commitUrls(string $prefix, $record, array $data)
{
    return test()->postJson(route("panel.{$prefix}.draft.commit", $record), ['data' => $data, 'rebase' => []]);
}

it('seeds one slug list per language on the edit page', function (Closure $make, string $prefix) {
    $record = $make();
    $url = $record->urls()->create(['language_id' => $this->en->id, 'slug' => 'stark', 'default' => true]);

    $this->get(route("panel.{$prefix}.edit", $record))
        ->assertInertia(fn (Assert $page) => $page
            ->where('formSliceValues.url:en', [['id' => $url->id, 'slug' => 'stark', 'default' => true]])
            ->where('formSliceValues.url:de', [])
        );
})->with('url records');

it('drafts slug edits and applies them only on commit', function (Closure $make, string $prefix) {
    $record = $make();
    $keep = $record->urls()->create(['language_id' => $this->en->id, 'slug' => 'stark', 'default' => true]);
    $record->urls()->create(['language_id' => $this->en->id, 'slug' => 'old-alias', 'default' => false]);

    // Rename the default, drop the alias, add a new default and a German slug.
    $data = [
        'url:en' => [
            ['id' => $keep->id, 'slug' => 'stark-hq', 'default' => false],
            ['id' => null, 'slug' => 'iron', 'default' => true],
        ],
        'url:de' => [['id' => null, 'slug' => 'stark-de', 'default' => false]],
    ];

    $this->patchJson(route("panel.{$prefix}.draft.update", $record), ['data' => $data])->assertOk();

    expect(slugRows($record, $this->en))->toBe([
        ['slug' => 'stark', 'default' => true],
        ['slug' => 'old-alias', 'default' => false],
    ]);

    commitUrls($prefix, $record, $data)->assertOk();

    expect(slugRows($record, $this->en))->toBe([
        ['slug' => 'stark-hq', 'default' => false],
        ['slug' => 'iron', 'default' => true],
    ])
        // A language's only slug is its default.
        ->and(slugRows($record, $this->de))->toBe([['slug' => 'stark-de', 'default' => true]])
        ->and($keep->refresh()->slug)->toBe('stark-hq')
        ->and(EditDraft::count())->toBe(0);
})->with('url records');

it('keeps exactly one default per language when the draft marks several', function () {
    $brand = Brand::factory()->create();

    $this->patchJson(route('panel.brands.draft.update', $brand), ['data' => ['url:en' => [
        ['id' => null, 'slug' => 'a', 'default' => true],
        ['id' => null, 'slug' => 'b', 'default' => true],
    ]]])->assertOk();

    expect(array_column(EditDraft::sole()->data['url:en'], 'default'))->toBe([true, false]);
});

it('lets a record swap its own slugs but refuses one another record of the type holds', function () {
    $brand = Brand::factory()->create();
    $a = $brand->urls()->create(['language_id' => $this->en->id, 'slug' => 'alpha', 'default' => true]);
    $b = $brand->urls()->create(['language_id' => $this->en->id, 'slug' => 'beta', 'default' => false]);

    Brand::factory()->create()->urls()->create(['language_id' => $this->en->id, 'slug' => 'taken', 'default' => true]);
    // Another element type may use the same slug.
    Product::factory()->create()->urls()->create(['language_id' => $this->en->id, 'slug' => 'gamma', 'default' => true]);

    commitUrls('brands', $brand, ['url:en' => [
        ['id' => $a->id, 'slug' => 'taken', 'default' => true],
        ['id' => $b->id, 'slug' => 'beta', 'default' => false],
    ]])->assertUnprocessable()->assertJsonValidationErrors(['url:en.0.slug']);

    commitUrls('brands', $brand, ['url:en' => [
        ['id' => $a->id, 'slug' => 'beta', 'default' => true],
        ['id' => $b->id, 'slug' => 'gamma', 'default' => false],
    ]])->assertOk();

    expect(slugRows($brand, $this->en))->toBe([
        ['slug' => 'beta', 'default' => true],
        ['slug' => 'gamma', 'default' => false],
    ]);
});

it('refuses duplicate, malformed and foreign slugs', function () {
    $brand = Brand::factory()->create();
    $foreign = Brand::factory()->create()->urls()->create(['language_id' => $this->en->id, 'slug' => 'theirs', 'default' => true]);

    commitUrls('brands', $brand, ['url:en' => [
        ['id' => null, 'slug' => 'same', 'default' => true],
        ['id' => null, 'slug' => 'same', 'default' => false],
        ['id' => null, 'slug' => 'Not A Slug', 'default' => false],
        ['id' => $foreign->id, 'slug' => 'mine-now', 'default' => false],
    ]])->assertUnprocessable()
        ->assertJsonValidationErrors(['url:en.0.slug', 'url:en.2.slug', 'url:en.3.id']);

    expect($foreign->refresh()->slug)->toBe('theirs')
        ->and($brand->urls()->count())->toBe(0);
});

it('no longer exposes immediate url endpoints', function () {
    foreach (['products', 'brands', 'collections'] as $prefix) {
        foreach (['store', 'update', 'destroy'] as $action) {
            expect(Route::has("panel.{$prefix}.urls.{$action}"))->toBeFalse();
        }
    }
});
