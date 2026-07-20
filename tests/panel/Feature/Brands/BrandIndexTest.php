<?php

use Illuminate\Support\Facades\Config;
use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Generators\UrlGenerator;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    // Brand creation triggers the HasUrls generator, which needs a default language.
    Language::factory()->create(['default' => true]);
});

it('redirects guests to the login screen', function () {
    $this->get(route('panel.brands.index'))->assertRedirect(route('panel.login'));
});

it('renders the brands index with rows', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Brand::factory()->count(3)->create();

    $this->get(route('panel.brands.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('brands/Index')
            ->has('brands.data', 3)
            ->has('brands.data.0', fn (Assert $row) => $row
                ->hasAll(['id', 'name', 'handle', 'thumbnail', 'short_description', 'collections_count', 'products_count', 'status', 'status_label', 'edit_url', '_actions'])
                ->etc()
            )
            ->has('columns')
            ->has('tableBulkActions', 2)
            ->has('urls.create')
        );
});

it('searches by name, handle and url slug', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Config::set('lunar.urls.generator', UrlGenerator::class);

    Brand::factory()->create(['name' => 'Stark Industries', 'handle' => 'stark']);
    Brand::factory()->create(['name' => 'Wayne Enterprises', 'handle' => 'wayne']);

    $this->get(route('panel.brands.index', ['q' => 'Stark']))
        ->assertInertia(fn (Assert $page) => $page->has('brands.data', 1));

    $this->get(route('panel.brands.index', ['q' => 'wayne']))
        ->assertInertia(fn (Assert $page) => $page->has('brands.data', 1));

    $this->get(route('panel.brands.index', ['q' => 'wayne-enterprises']))
        ->assertInertia(fn (Assert $page) => $page->has('brands.data', 1));
});

it('filters by status', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Brand::factory()->active()->count(2)->create();
    Brand::factory()->draft()->create();

    $this->get(route('panel.brands.index', ['status' => 'draft']))
        ->assertInertia(fn (Assert $page) => $page->has('brands.data', 1));

    $this->get(route('panel.brands.index', ['status' => 'nonsense']))
        ->assertInertia(fn (Assert $page) => $page->has('brands.data', 3));
});

it('sorts by the allow-listed columns and falls back on unknown sorts', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $alpha = Brand::factory()->create(['name' => 'Alpha']);
    $zulu = Brand::factory()->create(['name' => 'Zulu']);
    Product::factory()->create(['brand_id' => $zulu->id]);

    $this->get(route('panel.brands.index', ['sort' => 'name', 'direction' => 'asc']))
        ->assertInertia(fn (Assert $page) => $page->where('brands.data.0.id', $alpha->id));

    $this->get(route('panel.brands.index', ['sort' => 'products_count', 'direction' => 'desc']))
        ->assertInertia(fn (Assert $page) => $page->where('brands.data.0.id', $zulu->id));

    $this->get(route('panel.brands.index', ['sort' => 'evil_column']))
        ->assertOk();
});

it('paginates fifteen per page', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Brand::factory()->count(16)->create();

    $this->get(route('panel.brands.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('brands.data', 15)
            ->where('brands.total', 16)
        );
});
