<?php

namespace Lunar\Search;

use Illuminate\Support\Manager;
use Lunar\Models\Product;
use Lunar\Search\Contracts\SearchManagerContract;
use Lunar\Search\Engines\AbstractEngine;
use Lunar\Search\Engines\DatabaseEngine;
use Lunar\Search\Engines\MeilisearchEngine;
use Lunar\Search\Engines\TypesenseEngine;

class SearchManager extends Manager implements SearchManagerContract
{
    protected string $model = Product::class;

    public function createDatabaseDriver()
    {
        return $this->buildProvider(DatabaseEngine::class);
    }

    public function createMeilisearchDriver()
    {
        return $this->buildProvider(MeilisearchEngine::class);
    }

    public function createTypesenseDriver()
    {
        return $this->buildProvider(TypesenseEngine::class);
    }

    public function buildProvider($provider)
    {
        return $this->container->make($provider);
    }

    public function model(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function driver($driver = null): AbstractEngine
    {
        if ($driver) {
            return parent::driver($driver);
        }

        $engineMap = config('lunar.search.engine_map');

        $engine = $engineMap[$this->model] ?? $driver;

        return parent::driver($engine);
    }

    public function getDefaultDriver()
    {
        return config('scout.driver', 'database');
    }
}
