<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductOption;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');
});

test('the product options index renders with translated names and counts', function () {
    $option = ProductOption::factory()->create(['name' => ['en' => 'Colour'], 'handle' => 'colour']);
    $option->values()->create(['name' => ['en' => 'Red'], 'position' => 1]);
    ProductOption::factory()->create(['name' => ['en' => 'Size'], 'handle' => 'size']);

    $this->get(route('panel.settings.product-options.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/product-options/Index')
            ->has('productOptions.data', 2)
            ->where('productOptions.data.0.name', 'Colour')
            ->where('productOptions.data.0.values_count', 1)
            ->where('productOptions.data.1.name', 'Size')
            ->has('urls.store')
        );
});

test('a product option can be created and redirects to its edit screen', function () {
    $this->post(route('panel.settings.product-options.store'), [
        'name' => 'Colour',
    ])->assertRedirect()
        ->assertSessionHas('success');

    $option = ProductOption::where('handle', 'colour')->first();

    expect($option)->not->toBeNull();
    expect($option->translate('name'))->toBe('Colour');
    expect($option->shared)->toBeTrue();
});

test('a colliding auto-generated handle is rejected as a validation error', function () {
    ProductOption::factory()->create(['name' => ['en' => 'Colour'], 'handle' => 'colour']);

    $this->post(route('panel.settings.product-options.store'), [
        'name' => 'Colour',
    ])->assertSessionHasErrors('handle');

    expect(ProductOption::count())->toBe(1);
});

test('the product option edit screen renders with its values', function () {
    $option = ProductOption::factory()->create(['name' => ['en' => 'Colour'], 'handle' => 'colour']);
    $option->values()->create(['name' => ['en' => 'Red'], 'position' => 1]);

    $this->get(route('panel.settings.product-options.edit', $option))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/product-options/Edit')
            ->where('productOption.name', 'Colour')
            ->has('values', 1)
            ->where('values.0.name', 'Red')
            ->where('values.0.inUse', false)
            ->where('hasProducts', false)
        );
});

test('updating a product option syncs its values', function () {
    $option = ProductOption::factory()->create(['name' => ['en' => 'Colour'], 'handle' => 'colour']);
    $kept = $option->values()->create(['name' => ['en' => 'Red'], 'position' => 1]);
    $option->values()->create(['name' => ['en' => 'Green'], 'position' => 2]);

    $this->put(route('panel.settings.product-options.update', $option), [
        'name' => 'Colour',
        'handle' => 'colour',
        'values' => [
            ['id' => $kept->id, 'name' => 'Crimson', 'position' => 1],
            ['name' => 'Blue', 'position' => 2],
        ],
    ])->assertRedirect()
        ->assertSessionHas('success');

    $names = $option->values()->orderBy('position')->get()->map(fn ($value) => $value->translate('name'))->all();

    expect($names)->toBe(['Crimson', 'Blue']);
    expect($kept->fresh()->translate('name'))->toBe('Crimson');
});

test('updating preserves other locale translations', function () {
    $option = ProductOption::factory()->create(['name' => ['en' => 'Colour', 'fr' => 'Couleur'], 'handle' => 'colour']);

    $this->put(route('panel.settings.product-options.update', $option), [
        'name' => 'Colours',
        'handle' => 'colour',
    ])->assertRedirect();

    $option->refresh();

    expect($option->translate('name', 'en'))->toBe('Colours');
    expect($option->translate('name', 'fr'))->toBe('Couleur');
});

test('a value carried by variants cannot be removed', function () {
    Language::factory()->create(['default' => true]);

    $option = ProductOption::factory()->create(['name' => ['en' => 'Colour'], 'handle' => 'colour']);
    $value = $option->values()->create(['name' => ['en' => 'Red'], 'position' => 1]);
    $variant = ProductVariant::factory()->create();
    $value->variants()->attach($variant->id);

    $this->from(route('panel.settings.product-options.edit', $option))
        ->put(route('panel.settings.product-options.update', $option), [
            'name' => 'Colour',
            'handle' => 'colour',
            'values' => [],
        ])->assertRedirect(route('panel.settings.product-options.edit', $option))
        ->assertSessionHas('error', __('panel::product_options.value_delete_blocked'));

    expect($option->values()->count())->toBe(1);
});

test('an unused product option can be deleted along with its values', function () {
    $option = ProductOption::factory()->create(['name' => ['en' => 'Colour'], 'handle' => 'colour']);
    $option->values()->create(['name' => ['en' => 'Red'], 'position' => 1]);

    $this->delete(route('panel.settings.product-options.destroy', $option))
        ->assertRedirect(route('panel.settings.product-options.index'))
        ->assertSessionHas('success');

    expect(ProductOption::find($option->id))->toBeNull();
});

test('an option linked to products cannot be deleted and shows a flash error', function () {
    Language::factory()->create(['default' => true]);

    $option = ProductOption::factory()->create(['name' => ['en' => 'Colour'], 'handle' => 'colour']);
    $product = Product::factory()->create();
    $option->products()->attach($product->id, ['position' => 1]);

    $this->from(route('panel.settings.product-options.edit', $option))
        ->delete(route('panel.settings.product-options.destroy', $option))
        ->assertRedirect(route('panel.settings.product-options.edit', $option))
        ->assertSessionHas('error', __('panel::product_options.delete_blocked'));

    expect(ProductOption::find($option->id))->not->toBeNull();
});
