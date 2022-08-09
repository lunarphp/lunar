<?php

namespace GetCandy\Hub\Tables\Columns;

class AttributeColumn extends TextColumn
{
    /**
     * {@inheritDoc}
     */
    protected string $view = 'adminhub::tables.columns.attributes';

    protected function getStateFromRecord()
    {
        return $this->getRecord()->translateAttribute(
            $this->getName()
        );
    }
}
