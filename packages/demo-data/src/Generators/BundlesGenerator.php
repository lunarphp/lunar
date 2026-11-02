<?php

namespace Lunar\DemoData\Generators;

use Illuminate\Support\Collection;
use Lunar\Bundles\Contracts\Actions\DefinesBundle;
use Lunar\Bundles\Enums\BundlePricing;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\DemoData\Support\DemoContext;

/**
 * Adds two bundles built from the catalogue's products when `lunarphp/bundles`
 * is installed: a fixed kit priced from its components and a configurable gift
 * set at a fixed price. A no-op without the package or with too few products.
 */
class BundlesGenerator implements Generator
{
    protected const KIT_SKU = 'BND-KIT-001';

    protected const SET_SKU = 'BND-SET-002';

    public function generate(DemoContext $context): void
    {
        if (! app()->bound(DefinesBundle::class)) {
            return;
        }

        $context->reseed();

        // On a re-run the catalogue generator creates nothing, so fall back to
        // the products already in the store (never a bundle's own product).
        /** @var Collection<int, Product> $products */
        $products = collect($context->get('products', collect()))
            ->whenEmpty(fn () => Product::query()
                ->whereHas('variants', fn ($query) => $query
                    ->whereNotIn('sku', [self::KIT_SKU, self::SET_SKU])
                    ->where('stock_available', '>', 0))
                ->orderBy('id')
                ->limit(3)
                ->get())
            ->filter(fn (Product $product) => $product->variants()->count() > 0)
            ->values();

        $context->set('products', $products);

        if ($products->count() < 3) {
            return;
        }

        $components = $products->take(3)->map(fn (Product $product) => $product->variants()->first());

        $this->kit($context, $components);
        $this->giftSet($context, $components);
    }

    /**
     * @param  Collection<int, ProductVariant>  $components
     */
    protected function kit(DemoContext $context, Collection $components): void
    {
        $variant = $this->bundleVariant($context, self::KIT_SKU, 'Essentials Kit', 'Three of our favourites, together at a saving.', 0.0);

        if (! $variant) {
            return;
        }

        $bundle = app(DefinesBundle::class)->execute($variant, BundlePricing::Components, 10.0);

        $bundle->syncComponents(
            $components->values()->map(fn (ProductVariant $component, int $index) => [
                'variant' => $component,
                'quantity' => 1,
                'position' => $index,
            ])->all()
        );
    }

    /**
     * @param  Collection<int, ProductVariant>  $components
     */
    protected function giftSet(DemoContext $context, Collection $components): void
    {
        $variant = $this->bundleVariant($context, self::SET_SKU, 'Gift Set', 'One of our staples plus your pick of a second.', 60.0);

        if (! $variant) {
            return;
        }

        $bundle = app(DefinesBundle::class)->execute($variant, BundlePricing::Fixed);

        $bundle->syncGroups([
            ['name' => ['en' => 'Choose your extra'], 'min_selections' => 1, 'max_selections' => 1, 'position' => 0],
        ]);

        $group = $bundle->groups()->first();

        $bundle->syncComponents([
            ['variant' => $components[0], 'quantity' => 1, 'position' => 0],
            ['variant' => $components[1], 'quantity' => 1, 'group' => $group, 'default' => true, 'position' => 1],
            ['variant' => $components[2], 'quantity' => 1, 'group' => $group, 'position' => 2],
        ]);
    }

    /**
     * Create the bundle's own product and variant, keyed on SKU so a re-run skips it.
     */
    protected function bundleVariant(DemoContext $context, string $sku, string $name, string $description, float $price): ?ProductVariant
    {
        if (ProductVariant::query()->where('sku', $sku)->exists()) {
            return null;
        }

        $product = Product::create([
            'product_type_id' => $context->get('products')->first()->product_type_id,
            'status' => 'published',
            'name' => collect(['en' => $name]),
            'description' => collect(['en' => $description]),
            'short_description' => collect(['en' => $description]),
            'attribute_data' => collect(),
        ]);

        $product->scheduleChannel($context->get('channel'), now());

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'tax_class_id' => $context->get('taxClass')->id,
            'sku' => $sku,
            'unit_quantity' => 1,
            'shippable' => true,
            'attribute_data' => collect(),
        ]);

        // A fixed-price bundle needs its own price rows; a components-priced
        // bundle gets them materialised by the package.
        if ($price > 0) {
            foreach ($context->get('currencies') as $currency) {
                $factor = $currency->exchange_rate * (10 ** $currency->decimal_places);

                $variant->prices()->create([
                    'price' => (int) round($price * $factor),
                    'currency_id' => $currency->id,
                    'min_quantity' => 1,
                ]);
            }
        }

        return $variant;
    }
}
