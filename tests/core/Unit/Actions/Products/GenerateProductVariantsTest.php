<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Products\GenerateProductVariants;
use Lunar\Core\Exceptions\ProductActionException;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductOption;
use Lunar\Core\Models\ProductOptionValue;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
});

function sharedSizeOption(array $values = ['Small', 'Medium']): ProductOption
{
    $option = ProductOption::factory()->create(['name' => ['en' => 'Size'], 'shared' => true]);

    foreach ($values as $index => $name) {
        ProductOptionValue::factory()->create([
            'product_option_id' => $option->id,
            'name' => ['en' => $name],
            'position' => ($index + 1) * 10,
        ]);
    }

    return $option->refresh();
}

function sharedSelection(ProductOption $option, ?array $valueIds = null): array
{
    return [
        'type' => 'shared',
        'id' => $option->id,
        'value_ids' => $valueIds ?? $option->values->pluck('id')->all(),
    ];
}

test('adopts the sole valueless variant into the first combination', function () {
    $product = Product::factory()->create();
    $original = ProductVariant::factory()->create(['product_id' => $product->id, 'sku' => 'KEEP-ME']);
    $option = sharedSizeOption();

    $result = app(GenerateProductVariants::class)->execute($product, [sharedSelection($option)]);

    expect($result)->toBe(['kept' => 1, 'added' => 1, 'removed' => 0])
        ->and($product->variants()->count())->toBe(2)
        ->and($original->refresh()->sku)->toBe('KEEP-ME')
        ->and($original->values->pluck('id')->all())->toBe([$option->values->first()->id]);

    expect($product->productOptions()->get()->pluck('id')->all())->toBe([$option->id]);
});

test('regeneration keeps matching variants and diffs the rest', function () {
    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);
    $option = sharedSizeOption(['Small', 'Medium', 'Large']);
    $action = app(GenerateProductVariants::class);

    [$small, $medium, $large] = $option->values->sortBy('position')->values();

    $action->execute($product, [sharedSelection($option, [$small->id, $medium->id])]);

    $keptVariant = $product->variants()->with('values')->get()
        ->first(fn (ProductVariant $variant) => $variant->values->pluck('id')->all() === [$small->id]);

    $result = $action->execute($product, [sharedSelection($option, [$small->id, $large->id])]);

    expect($result)->toBe(['kept' => 1, 'added' => 1, 'removed' => 1])
        ->and($product->variants()->count())->toBe(2)
        ->and($product->variants()->pluck('id'))->toContain($keptVariant->id);
});

test('refuses when a removal has order history', function () {
    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);
    $option = sharedSizeOption(['Small', 'Medium', 'Large']);
    $action = app(GenerateProductVariants::class);

    [$small, $medium, $large] = $option->values->sortBy('position')->values();

    $action->execute($product, [sharedSelection($option, [$small->id, $medium->id])]);

    $ordered = $product->variants()->with('values')->get()
        ->first(fn (ProductVariant $variant) => $variant->values->pluck('id')->all() === [$medium->id]);

    OrderLine::factory()->create([
        'purchasable_type' => $ordered->getMorphClass(),
        'purchasable_id' => $ordered->id,
    ]);

    expect(fn () => $action->execute($product, [sharedSelection($option, [$small->id, $large->id])]))
        ->toThrow(ProductActionException::class);

    expect($product->variants()->count())->toBe(2);
});

test('caps attached options and requires selected values', function () {
    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);
    $action = app(GenerateProductVariants::class);

    $selections = collect(range(1, 4))
        ->map(fn () => sharedSelection(sharedSizeOption()))
        ->all();

    expect(fn () => $action->execute($product, $selections))
        ->toThrow(ProductActionException::class);

    $option = sharedSizeOption();

    expect(fn () => $action->execute($product, [sharedSelection($option, [])]))
        ->toThrow(ProductActionException::class);
});

test('rejects value ids that do not belong to the shared option', function () {
    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);
    $option = sharedSizeOption();
    $foreign = sharedSizeOption(['Red', 'Blue']);

    expect(fn () => app(GenerateProductVariants::class)->execute($product, [
        sharedSelection($option, [$foreign->values->first()->id]),
    ]))->toThrow(ProductActionException::class);
});

test('creates, renames and prunes exclusive options through the sync', function () {
    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);
    $action = app(GenerateProductVariants::class);

    $action->execute($product, [[
        'type' => 'exclusive',
        'id' => null,
        'name' => ['en' => 'Strap length'],
        'values' => [
            ['id' => null, 'name' => ['en' => 'Short']],
            ['id' => null, 'name' => ['en' => 'Long']],
        ],
    ]]);

    $exclusive = $product->productOptions()->exclusive()->with('values')->first();

    expect($exclusive)->not->toBeNull()
        ->and($exclusive->shared)->toBeFalse()
        ->and($exclusive->values)->toHaveCount(2)
        ->and($product->variants()->count())->toBe(2);

    $values = $exclusive->values->sortBy('position')->values();

    $action->execute($product, [[
        'type' => 'exclusive',
        'id' => $exclusive->id,
        'name' => ['en' => 'Strap size'],
        'values' => [
            ['id' => $values[0]->id, 'name' => ['en' => 'Shortish']],
            ['id' => $values[1]->id, 'name' => ['en' => 'Long']],
        ],
    ]]);

    expect($exclusive->refresh()->translate('name'))->toBe('Strap size')
        ->and($values[0]->refresh()->translate('name'))->toBe('Shortish')
        ->and($product->variants()->count())->toBe(2);

    $shared = sharedSizeOption();

    $action->execute($product, [sharedSelection($shared)]);

    $this->assertDatabaseMissing('lunar_product_options', ['id' => $exclusive->id]);
    $this->assertDatabaseMissing('lunar_product_option_values', ['id' => $values[0]->id]);
});

test('an empty selection collapses to a single variant', function () {
    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);
    $option = sharedSizeOption();
    $action = app(GenerateProductVariants::class);

    $action->execute($product, [sharedSelection($option)]);

    expect($product->variants()->count())->toBe(2);

    $survivor = $product->variants()->orderBy('id')->first();

    $result = $action->execute($product, []);

    expect($result)->toBe(['kept' => 1, 'added' => 0, 'removed' => 1])
        ->and($product->variants()->pluck('id')->all())->toBe([$survivor->id])
        ->and($survivor->values()->count())->toBe(0)
        ->and($product->productOptions()->count())->toBe(0);

    $this->assertDatabaseHas('lunar_product_options', ['id' => $option->id]);
});

test('added variants copy defaults, clone base prices and get a suggested sku', function () {
    $currency = Currency::factory()->create(['default' => true, 'decimal_places' => 2]);
    $product = Product::factory()->create(['name' => collect(['en' => 'Alpha Widget'])]);
    $template = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'shippable' => false,
        'min_quantity' => 5,
        'quantity_increment' => 5,
        'unit_quantity' => 2,
    ]);

    Price::factory()->create([
        'priceable_type' => $template->getMorphClass(),
        'priceable_id' => $template->id,
        'currency_id' => $currency->id,
        'customer_group_id' => null,
        'min_quantity' => 1,
        'price' => 1099,
    ]);

    $option = sharedSizeOption();

    app(GenerateProductVariants::class)->execute($product, [sharedSelection($option)]);

    $added = $product->variants()->orderBy('id')->get()->last();

    expect($added->id)->not->toBe($template->id)
        ->and($added->shippable)->toBeFalse()
        ->and($added->min_quantity)->toBe(5)
        ->and($added->quantity_increment)->toBe(5)
        ->and($added->unit_quantity)->toBe(2)
        ->and($added->tax_class_id)->toBe($template->tax_class_id)
        ->and($added->sku)->toContain('MEDIUM')
        ->and($added->basePrices()->first()->price)->toBe(1099);
});
