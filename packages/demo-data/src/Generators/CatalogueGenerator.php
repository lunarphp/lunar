<?php

namespace Lunar\DemoData\Generators;

use Illuminate\Support\Collection;
use Lunar\Core\Enums\StockMovementType;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Collection as ProductCollection;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductType;
use Lunar\Core\Models\ProductVariant;
use Lunar\DemoData\Support\DemoContext;

/**
 * Builds the catalogue: a product type, a collection group with one collection
 * per category, and the products themselves — each a single-variant product
 * with per-currency prices, opening stock, and a bundled placeholder image.
 *
 * Keyed on the variant SKU so a re-run skips products that already exist; the
 * curated copy fixture is cycled (with a numeric suffix) to reach larger scales.
 */
class CatalogueGenerator implements Generator
{
    public function generate(DemoContext $context): void
    {
        $productType = $this->productType();
        $group = $this->collectionGroup();
        $collections = collect();
        $brands = collect();

        $fixtures = $this->fixtures();
        $images = $this->images();
        $target = $context->count('products', $fixtures->count());

        $channel = $context->get('channel');
        $location = $context->get('location');
        $currencies = $context->get('currencies');
        $taxClass = $context->get('taxClass');

        $created = collect();

        for ($index = 0; $index < $target; $index++) {
            $fixture = $fixtures[$index % $fixtures->count()];
            $copy = intdiv($index, $fixtures->count());
            $suffix = $copy > 0 ? ' '.($copy + 1) : '';

            $sku = $fixture['sku'].($copy > 0 ? '-'.($copy + 1) : '');

            if (ProductVariant::query()->where('sku', $sku)->exists()) {
                continue;
            }

            $collection = $collections->get($fixture['collection'])
                ?? $collections->put(
                    $fixture['collection'],
                    $this->collection($group, $fixture['collection'])
                )->get($fixture['collection']);

            $brand = $brands->get($fixture['brand'])
                ?? $brands->put($fixture['brand'], $this->brand($fixture['brand']))->get($fixture['brand']);

            $product = Product::create([
                'product_type_id' => $productType->id,
                'brand_id' => $brand->id,
                'status' => 'published',
                'name' => collect(['en' => $fixture['name'].$suffix]),
                'description' => collect(['en' => $fixture['description']]),
                'short_description' => collect(['en' => $fixture['short_description']]),
                'attribute_data' => collect(),
            ]);

            $product->scheduleChannel($channel, now());
            $collection->products()->attach($product->id, ['position' => $collection->products()->count() + 1]);

            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'tax_class_id' => $taxClass->id,
                'sku' => $sku,
                'unit_quantity' => 1,
                'shippable' => true,
                'attribute_data' => collect(),
            ]);

            $this->prices($variant, $currencies, (float) $fixture['price'], isset($fixture['list_price']) ? (float) $fixture['list_price'] : null);
            $variant->adjustStock($location, $this->stockQuantity($context, $index), StockMovementType::OpeningBalance);
            $this->attachImage($product, $images[$index % count($images)]);

            $created->push($product);
        }

        $context->set('products', $created);
    }

    protected function productType(): ProductType
    {
        return ProductType::query()->firstOrCreate(['name' => 'General']);
    }

    protected function collectionGroup(): CollectionGroup
    {
        return CollectionGroup::query()->firstOrCreate(
            ['handle' => 'shop'],
            ['name' => 'Shop'],
        );
    }

    protected function collection(CollectionGroup $group, string $name): ProductCollection
    {
        return ProductCollection::query()
            ->where('collection_group_id', $group->id)
            ->where('name->en', $name)
            ->first()
            ?? ProductCollection::create([
                'collection_group_id' => $group->id,
                'name' => collect(['en' => $name]),
                'status' => 'published',
                'attribute_data' => collect(),
            ]);
    }

    protected function brand(string $name): Brand
    {
        return Brand::query()->firstOrCreate(['name' => $name]);
    }

    /**
     * @param  Collection<int, Currency>  $currencies
     */
    protected function prices(ProductVariant $variant, Collection $currencies, float $price, ?float $listPrice): void
    {
        foreach ($currencies as $currency) {
            $factor = $currency->exchange_rate * (10 ** $currency->decimal_places);

            $variant->prices()->create([
                'price' => (int) round($price * $factor),
                'list_price' => $listPrice !== null ? (int) round($listPrice * $factor) : null,
                'currency_id' => $currency->id,
                'min_quantity' => 1,
            ]);
        }
    }

    /**
     * Vary opening stock so out-of-stock and low-stock states are visible.
     */
    protected function stockQuantity(DemoContext $context, int $index): int
    {
        return match (true) {
            $index % 7 === 0 => 0,
            $index % 5 === 0 => $context->faker->numberBetween(1, 4),
            default => $context->faker->numberBetween(20, 250),
        };
    }

    protected function attachImage(Product $product, string $path): void
    {
        $product->addMedia($path)
            ->preservingOriginal()
            ->withCustomProperties(['primary' => true])
            ->toMediaCollection(
                config('lunar.media.collection', 'images'),
                (string) config('lunar.demo-data.asset_disk', 'public'),
            );
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function fixtures(): Collection
    {
        return collect(
            json_decode((string) file_get_contents(__DIR__.'/../../resources/fixtures/products.json'), true)
        );
    }

    /**
     * @return array<int, string>
     */
    protected function images(): array
    {
        return glob(__DIR__.'/../../resources/fixtures/images/*.jpg') ?: [];
    }
}
