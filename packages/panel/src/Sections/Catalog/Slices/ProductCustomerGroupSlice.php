<?php

namespace Lunar\Panel\Sections\Catalog\Slices;

use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Product;
use Lunar\Panel\Support\AvailabilitySchema;

class ProductCustomerGroupSlice extends AvailabilitySlice
{
    protected function side(): string
    {
        return AvailabilitySchema::CUSTOMER_GROUP_PREFIX;
    }

    public function commit(Model $record, array $values): void
    {
        /** @var Product $record */
        if ($values === []) {
            return;
        }

        $this->updatesProduct->execute(
            $record,
            [],
            customerGroups: $this->availabilitySchema->pivotRows($this->side(), $values),
        );
    }
}
