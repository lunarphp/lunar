<?php

namespace Lunar\Api\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Lunar\Models\Brand;
use Lunar\Api\Resources\BrandResource;

final class BrandCollectionProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        // Load them all; use orderBy if you want deterministic ordering
        $brands = Brand::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        // Map Eloquent -> DTO
        return $brands->map(fn (Brand $b) => $this->toResource($b))->all();
    }

    private function toResource(Brand $b): BrandResource
    {
        $r = new BrandResource();
        $r->id   = (string) $b->getKey();
        $r->name = $b->name;
        return $r;
    }
}
