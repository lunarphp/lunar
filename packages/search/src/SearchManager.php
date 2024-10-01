<?php

namespace Lunar\Search;

use Illuminate\Support\Manager;
use Lunar\PaymentTypes\OfflinePayment;
use Lunar\Search\Engines\TypesenseEngine;

class SearchManager extends Manager
{
    public function createDatabaseDriver()
    {
        dd(1);

        return $this->buildProvider(OfflinePayment::class);
    }

    public function createTypesenseDriver()
    {
        return $this->buildProvider(TypesenseEngine::class);
    }

    public function buildProvider($provider)
    {
        return $this->container->make($provider);
    }

    public function getDefaultDriver()
    {
        return config('lunar.search.engine', 'database');
    }
}
