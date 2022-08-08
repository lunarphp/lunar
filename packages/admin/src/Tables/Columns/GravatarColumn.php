<?php

namespace GetCandy\Hub\Tables\Columns;

class GravatarColumn extends TextColumn
{
    /**
     * {@inheritDoc}
     */
    protected string $view = 'adminhub::tables.columns.gravatar';

    protected function getStateFromRecord()
    {
        $record = $this->getRecord();

        return $record->email;
    }
}
