<?php

namespace Lunar\Admin\Support\Extending;

abstract class ResourceExtension extends BaseExtension
{
    public function getRelations(array $managers): array
    {
        return $managers;
    }

    public function extendTableFilters(array $filters): array
    {
        return $filters;
    }
}
