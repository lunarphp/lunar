<?php

namespace GetCandy\Hub\Tables\Columns;

use Filament\Tables\Columns\TextColumn;

class StatusColumn extends TextColumn
{
    /**
     * {@inheritDoc}
     */
    protected string $view = 'adminhub::tables.columns.status';

    protected function getStateFromRecord()
    {
        $record = $this->getRecord();
        $status = $record->getAttribute(
            $this->getName()
        );

        if ($record->deleted_at) {
            return 'deleted';
        }

        return $status;
    }
}
