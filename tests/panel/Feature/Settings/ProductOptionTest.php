<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductOption;
use Lunar\Core\Models\ProductOptionValue;
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
            ->where('productOptions.data.0.type', 'text')
            ->where('productOptions.data.0.values_count', 1)
            ->where('productOptions.data.1.name', 'Size')
            ->has('typeOptions', 3)
            ->has('urls.store')
        );
});

test('the index filters by type', function () {
    ProductOption::factory()->create(['name' => ['en' => 'Size'], 'handle' => 'size']);
    ProductOption::factory()->colour()->create(['name' => ['en' => 'Colour'], 'handle' => 'colour']);

    $this->get(route('panel.settings.product-options.index', ['type' => 'colour']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('productOptions.data', 1)
            ->where('productOptions.data.0.handle', 'colour')
        );
});

test('the index filters to unused options only', function () {
    Language::factory()->create(['default' => true]);

    $used = ProductOption::factory()->create(['name' => ['en' => 'Size'], 'handle' => 'size']);
    $used->products()->attach(Product::factory()->create()->id, ['position' => 1]);
    ProductOption::factory()->create(['name' => ['en' => 'Colour'], 'handle' => 'colour']);

    $this->get(route('panel.settings.product-options.index', ['unused' => 1]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('productOptions.data', 1)
            ->where('productOptions.data.0.handle', 'colour')
        );
});

test('the index shows only shared options by default and reveals dedicated ones when toggled off', function () {
    ProductOption::factory()->create(['name' => ['en' => 'Colour'], 'handle' => 'colour', 'shared' => true]);
    ProductOption::factory()->create(['name' => ['en' => 'Strap length'], 'handle' => 'strap-length', 'shared' => false]);

    $this->get(route('panel.settings.product-options.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('productOptions.data', 1)
            ->where('productOptions.data.0.handle', 'colour')
            ->where('filters.sharedOnly', true)
        );

    $this->get(route('panel.settings.product-options.index', ['shared' => 0]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('productOptions.data', 2)
            ->where('filters.sharedOnly', false)
        );
});

test('a dedicated product option can be promoted to shared', function () {
    $option = ProductOption::factory()->create(['name' => ['en' => 'Strap length'], 'handle' => 'strap-length', 'shared' => false]);

    $this->put(route('panel.settings.product-options.update', $option), [
        'name' => ['en' => 'Strap length'],
        'handle' => 'strap-length',
        'shared' => true,
    ])->assertRedirect()->assertSessionHas('success');

    expect($option->fresh()->shared)->toBeTrue();
});

test('a shared product option cannot be demoted to dedicated', function () {
    $option = ProductOption::factory()->create(['name' => ['en' => 'Colour'], 'handle' => 'colour', 'shared' => true]);

    $this->put(route('panel.settings.product-options.update', $option), [
        'name' => ['en' => 'Colour'],
        'handle' => 'colour',
        'shared' => false,
    ])->assertRedirect();

    expect($option->fresh()->shared)->toBeTrue();
});

test('a product option can be created and redirects to its edit screen', function () {
    $this->post(route('panel.settings.product-options.store'), [
        'name' => ['en' => 'Colour'],
        'type' => 'colour',
    ])->assertRedirect()
        ->assertSessionHas('success');

    $option = ProductOption::where('handle', 'colour')->first();

    expect($option)->not->toBeNull();
    expect($option->translate('name'))->toBe('Colour');
    expect($option->type)->toBe('colour');
    expect($option->shared)->toBeTrue();
});

test('a colliding auto-generated handle is rejected as a validation error', function () {
    ProductOption::factory()->create(['name' => ['en' => 'Colour'], 'handle' => 'colour']);

    $this->post(route('panel.settings.product-options.store'), [
        'name' => ['en' => 'Colour'],
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
            ->where('productOption.name.en', 'Colour')
            ->where('productOption.type', 'text')
            ->has('languages')
            ->has('values', 1)
            ->where('values.0.name.en', 'Red')
            ->where('values.0.inUse', false)
            ->where('hasProducts', false)
        );
});

test('updating a product option syncs its values', function () {
    $option = ProductOption::factory()->create(['name' => ['en' => 'Colour'], 'handle' => 'colour']);
    $kept = $option->values()->create(['name' => ['en' => 'Red'], 'position' => 1]);
    $option->values()->create(['name' => ['en' => 'Green'], 'position' => 2]);

    $this->put(route('panel.settings.product-options.update', $option), [
        'name' => ['en' => 'Colour'],
        'handle' => 'colour',
        'values' => [
            ['id' => $kept->id, 'name' => ['en' => 'Crimson'], 'position' => 1],
            ['name' => ['en' => 'Blue'], 'position' => 2],
        ],
    ])->assertRedirect()
        ->assertSessionHas('success');

    $names = $option->values()->orderBy('position')->get()->map(fn ($value) => $value->translate('name'))->all();

    expect($names)->toBe(['Crimson', 'Blue']);
    expect($kept->fresh()->translate('name'))->toBe('Crimson');
});

test('updating a colour option stores the value colour on meta', function () {
    $option = ProductOption::factory()->colour()->create(['name' => ['en' => 'Colour'], 'handle' => 'colour']);

    $this->put(route('panel.settings.product-options.update', $option), [
        'name' => ['en' => 'Colour'],
        'handle' => 'colour',
        'type' => 'colour',
        'values' => [
            ['name' => ['en' => 'Navy'], 'position' => 1, 'colour' => '#1f2a44'],
        ],
    ])->assertRedirect()->assertSessionHas('success');

    expect($option->values()->first()->meta['colour'])->toBe('#1F2A44');
});

test('an invalid colour is rejected', function () {
    $option = ProductOption::factory()->colour()->create(['name' => ['en' => 'Colour'], 'handle' => 'colour']);

    $this->put(route('panel.settings.product-options.update', $option), [
        'name' => ['en' => 'Colour'],
        'handle' => 'colour',
        'type' => 'colour',
        'values' => [
            ['name' => ['en' => 'Navy'], 'position' => 1, 'colour' => 'not-a-colour'],
        ],
    ])->assertSessionHasErrors('values.0.colour');
});

test('changing the type through the update endpoint clears value colours', function () {
    $option = ProductOption::factory()->colour()->create(['name' => ['en' => 'Colour'], 'handle' => 'colour']);
    $value = ProductOptionValue::factory()->colour('#1F2A44')->create(['product_option_id' => $option->id]);

    $this->put(route('panel.settings.product-options.update', $option), [
        'name' => ['en' => 'Colour'],
        'handle' => 'colour',
        'type' => 'text',
    ])->assertRedirect()->assertSessionHas('success');

    expect($option->fresh()->type)->toBe('text')
        ->and($value->fresh()->meta['colour'] ?? null)->toBeNull();
});

test('updating preserves other locale translations', function () {
    $option = ProductOption::factory()->create(['name' => ['en' => 'Colour', 'fr' => 'Couleur'], 'handle' => 'colour']);

    $this->put(route('panel.settings.product-options.update', $option), [
        'name' => ['en' => 'Colours'],
        'handle' => 'colour',
    ])->assertRedirect();

    $option->refresh();

    expect($option->translate('name', 'en'))->toBe('Colours');
    expect($option->translate('name', 'fr'))->toBe('Couleur');
});

test('a swatch image can be uploaded to a value and removed', function () {
    Storage::fake('public');

    $option = ProductOption::factory()->swatch()->create(['name' => ['en' => 'Material'], 'handle' => 'material']);
    $value = ProductOptionValue::factory()->create(['product_option_id' => $option->id]);
    $collection = config('lunar.media.collection');

    $this->post(route('panel.settings.product-options.values.swatch.store', [$option, $value]), [
        'file' => UploadedFile::fake()->image('cotton.png'),
    ])->assertRedirect()->assertSessionHas('success');

    expect($value->fresh()->getMedia($collection))->toHaveCount(1);

    $this->delete(route('panel.settings.product-options.values.swatch.destroy', [$option, $value]))
        ->assertRedirect()->assertSessionHas('success');

    expect($value->fresh()->getMedia($collection))->toHaveCount(0);
});

test('a value carried by variants cannot be removed', function () {
    Language::factory()->create(['default' => true]);

    $option = ProductOption::factory()->create(['name' => ['en' => 'Colour'], 'handle' => 'colour']);
    $value = $option->values()->create(['name' => ['en' => 'Red'], 'position' => 1]);
    $variant = ProductVariant::factory()->create();
    $value->variants()->attach($variant->id);

    $this->from(route('panel.settings.product-options.edit', $option))
        ->put(route('panel.settings.product-options.update', $option), [
            'name' => ['en' => 'Colour'],
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
