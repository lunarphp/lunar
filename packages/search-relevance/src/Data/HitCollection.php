<?php

namespace Lunar\SearchRelevance\Data;

use Illuminate\Support\Collection;

/** @extends Collection<int, Hit> */
final class HitCollection extends Collection
{
    /** @return array<int, int> */
    public function productIds(): array
    {
        return $this->map(fn (Hit $hit) => $hit->productId)->values()->all();
    }

    /** @return array<int, array<string, mixed>> */
    public function toArrays(): array
    {
        return $this->map(fn (Hit $hit) => $hit->toArray())->values()->all();
    }

    /** @param  array<int, array<string, mixed>>  $rows */
    public static function fromArrays(array $rows): self
    {
        return new self(array_map([Hit::class, 'fromArray'], $rows));
    }
}
