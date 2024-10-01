<?php

namespace Lunar\Search\Engines;

use Lunar\Models\Product;

abstract class AbstractEngine
{
    protected string $modelType = Product::class;

    protected ?string $query;

    protected \Closure $searchBuilder;

    public function query(string $query): AbstractEngine
    {
        $this->query = $query;

        return $this;
    }

    protected function getRawResults(): array
    {
        return $this->modelType::search($this->query, $this->searchBuilder)->raw();
    }

    public function get(): mixed
    {
        return collect();
    }
}
