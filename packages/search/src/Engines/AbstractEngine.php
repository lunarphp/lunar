<?php

namespace Lunar\Search\Engines;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Lunar\Models\Product;

abstract class AbstractEngine
{
    protected string $modelType = Product::class;

    protected ?string $query = null;

    protected array $filters = [];

    protected array $facets = [];

    protected int $perPage = 50;

    protected string $sort = '';

    public function filter(array $filters): self
    {
        foreach ($filters as $key => $value) {
            $this->addFilter($key, $value);
        }

        return $this;
    }

    public function addFilter($key, $value): self
    {
        $this->filters[$key] = $value;

        return $this;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function perPage(int $perPage): self
    {
        $this->perPage = $perPage;

        return $this;
    }

    public function getFacets(): array
    {
        return $this->facets;
    }

    public function setFacets(array $facets): self
    {
        $this->facets = $facets;

        return $this;
    }

    public function removeFacet(string $field, mixed $value = null): self
    {
        if (empty($this->facets[$field])) {
            return $this;
        }

        if (! $value) {
            unset($this->facets[$field]);

            return $this;
        }

        $this->facets[$field] = collect($this->facets[$field])->reject(
            fn ($faceValue) => $faceValue == $value
        )->toArray();

        return $this;
    }

    public function sort(string $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    public function query(string $query): AbstractEngine
    {
        $this->query = $query;

        return $this;
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }

    protected function getRawResults(\Closure $builder): LengthAwarePaginator
    {
        return $this->modelType::search($this->query, $builder)->paginateRaw(perPage: $this->perPage);
    }

    protected function getFacetConfig(?string $field = null): ?array
    {
        if (! $field) {
            return config('lunar.search.facets.'.$this->modelType);
        }

        return config('lunar.search.facets.'.$this->modelType.'.'.$field, []);
    }

    abstract public function get(): mixed;
}
