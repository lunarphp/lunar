<?php

use Filament\Schemas\Schema;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Filament\Schemas\Product\ProductForm;
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
