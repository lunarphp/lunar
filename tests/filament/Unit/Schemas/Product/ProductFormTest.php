<?php

use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Component;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductType;
use Lunar\Core\Models\ProductVariant;
use Lunar\Filament\Schemas\Product\ProductForm;
use Lunar\Filament\Schemas\ProductVariant\ProductVariantForm;
use Lunar\Tests\Filament\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true]);
    CustomerGroup::factory()->create(['default' => true]);
});

/**
 * Builds the status callouts plus the variant-attributes field inside a real
 * schema container so their `hidden()` closures resolve `$record` exactly the
 * way a resource page does.
 */
function productFormStatusComponents(?Product $record): array
{
    return array_values(
        Schema::make()
            ->record($record)
            ->components([
                ...ProductForm::getStatusShouts(),
                ProductForm::getVariantAttributeDataComponent(),
            ])
            ->getComponents(withHidden: true)
    );
}

it('evaluates every status component without a record on the create form', function () {
    // Regression: the Create page mounts the form with a null record. The
    // hidden() closures used to type-hint a non-nullable Model, so evaluating
    // them threw a TypeError before the page could render.
    $components = productFormStatusComponents(null);

    foreach ($components as $component) {
        expect($component->isHidden())->toBeTrue();
    }
});

it('keeps the status callouts working once a product exists', function () {
    $draft = Product::factory()->create(['status' => 'draft']);
    $published = Product::factory()->create(['status' => 'published']);

    // The draft banner (first callout) reads the record's status: it shows for
    // a draft product and hides for a published one.
    expect(productFormStatusComponents($draft)[0]->isHidden())->toBeFalse()
        ->and(productFormStatusComponents($published)[0]->isHidden())->toBeTrue();
});

function makeProductResourceHost(): Component
{
    return new class extends Component implements HasSchemas
    {
        use InteractsWithSchemas;

        public array $data = [];

        public static function getResource(): string
        {
            return new class
            {
                public static function getModel(): string
                {
                    return Product::class;
                }
            }::class;
        }
    };
}

it('fills the full ProductForm without a record on the create form', function () {
    $productType = ProductType::factory()->create();
    $productAttribute = Attribute::factory()->modelType(Product::morphName())->create();
    $variantAttribute = Attribute::factory()->modelType(ProductVariant::morphName())->create();
    $productType->attributeMapping()->attach([$productAttribute->id, $variantAttribute->id]);

    $schema = ProductForm::configure(
        Schema::make(makeProductResourceHost())
            ->model(Product::class)
            ->operation('create')
            ->statePath('data')
    );

    $schema->fill();

    $productComponent = $schema->getComponent('attributeData', withHidden: true);
    $variantComponent = $schema->getComponent('attributeDataProductVariant', withHidden: true);

    expect($productComponent)->not->toBeNull()
        ->and($productComponent->getChildComponents())->toHaveCount(1)
        ->and($variantComponent)->not->toBeNull()
        ->and($variantComponent->isHidden())->toBeTrue();
});

it('builds variant attribute sections when filling ProductForm for an existing single-variant product', function () {
    $productType = ProductType::factory()->create();
    $variantAttribute = Attribute::factory()->modelType(ProductVariant::morphName())->create();
    $productType->attributeMapping()->attach($variantAttribute);

    $product = Product::factory()->create(['product_type_id' => $productType->id]);
    ProductVariant::factory()->create(['product_id' => $product->id]);

    $schema = ProductForm::configure(
        Schema::make(makeProductResourceHost())
            ->model($product)
            ->operation('edit')
            ->statePath('data')
    );

    $schema->fill();

    $variantComponent = $schema->getComponent('attributeDataProductVariant', withHidden: true);

    expect($variantComponent)->not->toBeNull()
        ->and($variantComponent->isHidden())->toBeFalse()
        ->and($variantComponent->getChildComponents())->toHaveCount(1);
});

it('builds variant attribute sections when filling ProductVariantForm for a variant record', function () {
    $productType = ProductType::factory()->create();
    $variantAttribute = Attribute::factory()->modelType(ProductVariant::morphName())->create();
    $productType->attributeMapping()->attach($variantAttribute);

    $product = Product::factory()->create(['product_type_id' => $productType->id]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

    $livewire = new class extends Component implements HasSchemas
    {
        use InteractsWithSchemas;

        public array $data = [];

        public static function getResource(): string
        {
            return new class
            {
                public static function getModel(): string
                {
                    return ProductVariant::class;
                }
            }::class;
        }
    };

    $schema = ProductVariantForm::configure(
        Schema::make($livewire)
            ->model($variant)
            ->operation('edit')
            ->statePath('data')
    );

    $schema->fill();

    $variantComponent = $schema->getComponent('attributeData', withHidden: true);

    expect($variantComponent)->not->toBeNull()
        ->and($variantComponent->getChildComponents())->toHaveCount(1);
});
