<?php

namespace GetCandy\Hub\Tables;

use Closure;
use GetCandy\LivewireTables\Support\TableBuilder as LivewireTableBuilder;
use Illuminate\Support\Collection;

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
