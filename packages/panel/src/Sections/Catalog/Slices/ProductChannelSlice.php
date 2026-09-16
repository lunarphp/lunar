<?php

namespace Lunar\Panel\Sections\Catalog\Slices;

use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Product;
use Lunar\Panel\Support\AvailabilitySchema;

class ProductChannelSlice extends AvailabilitySlice
{
    protected function side(): string
    {
        return AvailabilitySchema::CHANNEL_PREFIX;
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
            channels: $this->availabilitySchema->pivotRows($this->side(), $values),
        );
    }
}
