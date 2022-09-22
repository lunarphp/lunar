<?php

namespace Lunar\Hub\Tables;

use Closure;
use Illuminate\Support\Collection;
use Lunar\LivewireTables\Support\TableBuilder as LivewireTableBuilder;

class TableBuilder extends LivewireTableBuilder
{
    /**
     * The query extenders for the table.
     *
     * @var Collection
     */
    protected Collection $queryExtenders;

    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        parent::__construct();

        $this->queryExtenders = collect();
    }

    /**
     * Add a query extender to the table.
     *
     * @param  Closure  $closure
     * @return void
     */
    public function extendQuery(Closure $closure)
    {
        $this->queryExtenders->push($closure);
    }
}
