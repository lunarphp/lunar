<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');

    Language::factory()->create(['default' => true]);
});

it('renders the edit page with brand, languages and draft props', function () {
    $brand = Brand::factory()->create(['name' => 'Stark Industries']);

    $this->get(route('panel.brands.edit', $brand))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('brands/Edit')
            ->where('brand.name', 'Stark Industries')
            ->hasAll(['brand.handle', 'brand.status', 'brand.short_description', 'brand.description', 'brand.products_count', 'brand.collections_count'])
            ->has('languages')
            ->where('draft', null)
            ->hasAll(['urls.update', 'urls.destroy', 'urls.draft', 'urls.draftCommit'])
        );
});

it('updates a brand through the update endpoint', function () {
    $brand = Brand::factory()->create(['name' => 'Old Name']);

    $this->put(route('panel.brands.update', $brand), [
        'name' => 'New Name',
        'handle' => 'new-name',
        'status' => 'draft',
        'short_description' => ['en' => 'A short blurb.'],
    ])->assertRedirect()->assertSessionHas('success', 'Brand updated.');

    $brand->refresh();

    expect($brand->name)->toBe('New Name')
        ->and($brand->handle)->toBe('new-name')
        ->and($brand->status->getValue())->toBe('draft')
        ->and($brand->translate('short_description'))->toBe('A short blurb.');
});

it('rejects a handle already used by another brand', function () {
    Brand::factory()->create(['handle' => 'taken']);
    $brand = Brand::factory()->create(['handle' => 'mine']);

    $this->put(route('panel.brands.update', $brand), [
        'name' => 'Renamed',
        'handle' => 'taken',
    ])->assertSessionHasErrors('handle');

    $this->put(route('panel.brands.update', $brand), [
        'name' => 'Renamed',
        'handle' => 'mine',
    ])->assertSessionDoesntHaveErrors('handle');
});

it('deletes a brand without products', function () {
    $brand = Brand::factory()->create();

    $this->delete(route('panel.brands.destroy', $brand))
        ->assertRedirect(route('panel.brands.index'))
        ->assertSessionHas('success', 'Brand deleted.');

    expect(Brand::find($brand->id))->toBeNull();
});

it('refuses to delete a brand with products', function () {
    $brand = Brand::factory()->create();
    Product::factory()->create(['brand_id' => $brand->id]);

    $this->from(route('panel.brands.edit', $brand))
        ->delete(route('panel.brands.destroy', $brand))
        ->assertRedirect(route('panel.brands.edit', $brand))
        ->assertSessionHas('error');

    expect(Brand::find($brand->id))->not->toBeNull();
});

it('sets the status on a selection of brands in bulk', function () {
    $brands = Brand::factory()->active()->count(2)->create();
    $untouched = Brand::factory()->active()->create();

    $this->post(route('panel.brands.bulk-status', ['status' => 'draft']), [
        'ids' => $brands->pluck('id')->all(),
    ])->assertRedirect()->assertSessionHas('success');

    expect($brands->map(fn (Brand $brand) => $brand->refresh()->status->getValue())->unique()->all())->toBe(['draft'])
        ->and($untouched->refresh()->status->getValue())->toBe('active');
});

it('rejects a bulk status outside the state list', function () {
    $brand = Brand::factory()->create();

    $this->post(route('panel.brands.bulk-status', ['status' => 'archived']), [
        'ids' => [$brand->id],
    ])->assertNotFound();
});

it('gates brand routes behind the catalog permission', function () {
    $this->actingAs(Staff::factory()->create(['admin' => false]), 'staff');

    $brand = Brand::factory()->create();

    $this->get(route('panel.brands.index'))->assertForbidden();
    $this->get(route('panel.brands.edit', $brand))->assertForbidden();
});
