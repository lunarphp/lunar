<?php

namespace GetCandy\Hub\Tables\Columns;

class OrderStatusColumn extends TextColumn
{
    /**
     * {@inheritDoc}
     */
    protected string $view = 'adminhub::tables.columns.order-status';

    protected function getStateFromRecord()
    {
        $record = $this->getRecord();
        return $record->status;
    }
}
