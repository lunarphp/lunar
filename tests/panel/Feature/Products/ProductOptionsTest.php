<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductOption;
use Lunar\Core\Models\ProductOptionValue;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Language::factory()->create(['default' => true, 'code' => 'en']);

    $this->product = Product::factory()->create();
    $this->variant = ProductVariant::factory()->create(['product_id' => $this->product->id]);
});

function sizeOption(array $names = ['Small', 'Medium']): ProductOption
{
    $option = ProductOption::factory()->create(['name' => ['en' => 'Size'], 'shared' => true]);

    foreach ($names as $index => $name) {
        ProductOptionValue::factory()->create([
            'product_option_id' => $option->id,
            'name' => ['en' => $name],
            'position' => ($index + 1) * 10,
        ]);
    }

    return $option->refresh();
}

it('generates variants from a shared option selection', function () {
    $option = sizeOption();

    $this->post(route('panel.products.options.generate', $this->product), [
        'selections' => [[
            'type' => 'shared',
            'id' => $option->id,
            'value_ids' => $option->values->pluck('id')->all(),
        ]],
    ])->assertRedirect()->assertSessionHas('success');

    expect($this->product->variants()->count())->toBe(2)
        ->and($this->product->productOptions()->count())->toBe(1);
});

it('creates exclusive options through the generate payload', function () {
    $this->post(route('panel.products.options.generate', $this->product), [
        'selections' => [[
            'type' => 'exclusive',
            'id' => null,
            'name' => 'Strap length',
            'values' => [
                ['id' => null, 'name' => 'Short'],
                ['id' => null, 'name' => 'Long'],
            ],
        ]],
    ])->assertRedirect()->assertSessionHas('success');

    $exclusive = $this->product->productOptions()->exclusive()->first();

    expect($exclusive)->not->toBeNull()
        ->and($this->product->variants()->count())->toBe(2);
});

it('collapses back to a simple product with an empty selection', function () {
    $option = sizeOption();

    $this->post(route('panel.products.options.generate', $this->product), [
        'selections' => [[
            'type' => 'shared',
            'id' => $option->id,
            'value_ids' => $option->values->pluck('id')->all(),
        ]],
    ]);

    $this->post(route('panel.products.options.generate', $this->product), [
        'selections' => [],
    ])->assertRedirect()->assertSessionHas('success');

    expect($this->product->variants()->count())->toBe(1)
        ->and($this->product->productOptions()->count())->toBe(0);
});

it('surfaces a locked-removal refusal as a flash error', function () {
    $option = sizeOption();
    [$small, $medium] = $option->values->sortBy('position')->values();

    $this->post(route('panel.products.options.generate', $this->product), [
        'selections' => [['type' => 'shared', 'id' => $option->id, 'value_ids' => [$small->id, $medium->id]]],
    ]);

    $ordered = $this->product->variants()->with('values')->get()
        ->first(fn (ProductVariant $variant) => $variant->values->pluck('id')->all() === [$medium->id]);

    OrderLine::factory()->create([
        'purchasable_type' => $ordered->getMorphClass(),
        'purchasable_id' => $ordered->id,
    ]);

    $this->post(route('panel.products.options.generate', $this->product), [
        'selections' => [['type' => 'shared', 'id' => $option->id, 'value_ids' => [$small->id]]],
    ])->assertRedirect()->assertSessionHas('error');

    expect($this->product->variants()->count())->toBe(2);
});

it('validates the selection shape and option cap', function () {
    $this->post(route('panel.products.options.generate', $this->product), [
        'selections' => [['type' => 'nonsense']],
    ])->assertSessionHasErrors('selections.0.type');

    $selections = collect(range(1, 4))->map(fn () => [
        'type' => 'shared',
        'id' => sizeOption()->id,
        'value_ids' => [1],
    ])->all();

    $this->post(route('panel.products.options.generate', $this->product), [
        'selections' => $selections,
    ])->assertSessionHasErrors('selections');
});

it('serves attached options and variant rows on the edit page', function () {
    $option = sizeOption();

    $this->post(route('panel.products.options.generate', $this->product), [
        'selections' => [[
            'type' => 'shared',
            'id' => $option->id,
            'value_ids' => $option->values->pluck('id')->all(),
        ]],
    ]);

    $this->get(route('panel.products.edit', $this->product))
        ->assertInertia(fn (Assert $page) => $page
            ->where('shape', 'multi')
            ->has('attachedOptions', 1)
            ->where('attachedOptions.0.id', $option->id)
            ->has('attachedOptions.0.selected_value_ids', 2)
            ->has('variants', 2)
            ->has('variants.0', fn (Assert $row) => $row
                ->hasAll(['id', 'label', 'value_ids', 'sku', 'price', 'stock', 'enabled', 'locked', 'edit_url'])
                ->etc()
            )
        );
});

it('searches shared options with their values', function () {
    sizeOption();
    ProductOption::factory()->create(['name' => ['en' => 'Material'], 'shared' => false]);

    $this->getJson(route('panel.catalog.product-options.search'))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Size')
        ->assertJsonCount(2, 'data.0.values');
});
