<?php

namespace Lunar\Tests\SearchRelevance\Support;

use Lunar\Core\Search\ProductIndexer;

/** A product indexer for a store whose shoppers also type supplier codes and barcodes. */
class SupplierCodeIndexer extends ProductIndexer
{
    public function __construct(protected array $exactMatchFields = ['skus', 'skus_normalised', 'mpns', 'eans']) {}

    public function getExactMatchFields(): array
    {
        return $this->exactMatchFields;
    }
}
