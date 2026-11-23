<?php

use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

/**
 * Spec 0087: on a drafted edit page, every control that applies immediately
 * rather than through the draft says so. Each file below holds such a
 * control and must render the "Applies immediately" marker, either through
 * Section's `immediate` prop or the AppliesImmediately component. A surface
 * leaves this list when it moves into the draft.
 */
dataset('immediate surfaces', [
    'stock adjustment' => 'panel/resources/js/components/InventoryCard.vue',
    'generate variants' => 'panel/resources/js/components/ProductOptionsBuilder.vue',
    'bulk variant actions' => 'panel/resources/js/components/VariantsTable.vue',
    'prices' => 'panel/resources/js/components/PricingEditor.vue',
    'url slugs' => 'panel/resources/js/components/UrlSlugs.vue',
    'images' => 'panel/resources/js/components/MediaManager.vue',
    'files' => 'panel/resources/js/components/FileManager.vue',
    'variant images' => 'panel/resources/js/components/VariantMediaPicker.vue',
    'product associations' => 'panel/resources/js/pages/products/Edit.vue',
    'collection hierarchy and products' => 'panel/resources/js/pages/collections/Edit.vue',
    'customer addresses and users' => 'panel/resources/js/pages/customers/Edit.vue',
    'bundles' => 'bundles/resources/js/components/BundleCard.vue',
]);

it('marks the surface as applying immediately', function (string $relative) {
    $contents = (string) file_get_contents(dirname(__DIR__, 3).'/packages/'.$relative);

    expect(preg_match('/<Section\b[^>]*\simmediate[\s>]/', $contents) === 1 || str_contains($contents, '<AppliesImmediately'))
        ->toBeTrue("{$relative} has an immediate control but no Applies immediately marker.");
})->with('immediate surfaces');
