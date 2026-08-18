<?php

namespace Lunar\Core\Actions\Products;

use Lunar\Core\Contracts\Actions\Products\CreatesProduct;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductType;
use Lunar\Core\Models\TaxClass;

/**
 * Create a product together with its initial variant, so every product owns
 * at least one sellable unit from birth. The variant's tax class comes from
 * the product type's default, falling back to the store default.
 */
class CreateProduct implements CreatesProduct
{
    public function execute(array $attributes): Product
    {
        return DB::transaction(function () use ($attributes): Product {
            /** @var Product $product */
            $product = Product::create($attributes);

            $product->variants()->create([
                'tax_class_id' => $this->defaultTaxClassId($product),
            ]);

            return $product;
        });
    }

    protected function defaultTaxClassId(Product $product): int
    {
        /** @var ProductType $type */
        $type = $product->productType;

        return $type->default_tax_class_id ?? TaxClass::getDefault()->id;
    }
}
