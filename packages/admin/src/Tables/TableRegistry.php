<?php

namespace GetCandy\Hub\Tables;

use Illuminate\Support\Collection;

class TableRegistry
{
    /**
     * The extended tables in the registry.
     *
     * @var array
     */
    public $tables = [];

    /**
     * Set and return the table extension.
     *
     * @param  string  $tableClass
     * @return TableExtension
     */
    public function on($tableClass)
    {
        $table = new TableExtension($tableClass);

        $this->tables[] = $table;

        return $table;
    }

    /**
     * Return the extensions for a given table.
     *
     * @param  string  $table
     * @return Collection
     */
    public function getExtensions($table)
    {
        return collect($this->tables)->filter(
            fn ($extension) => $extension->getTable() == $table
        );
    }
}
