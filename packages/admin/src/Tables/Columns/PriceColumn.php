<?php

namespace GetCandy\Hub\Tables\Columns;

class PriceColumn extends TextColumn
{
    /**
     * {@inheritDoc}
     */
    protected string $view = 'adminhub::tables.columns.price';

    protected function getStateFromRecord()
    {
        $record = $this->getRecord();

        $name = $this->getName();

        return $record->{$name}?->formatted();
    }
}
