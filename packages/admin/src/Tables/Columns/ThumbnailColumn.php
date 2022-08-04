<?php

namespace GetCandy\Hub\Tables\Columns;

use Filament\Tables\Columns\ImageColumn;

class ThumbnailColumn extends ImageColumn
{
    /**
     * {@inheritDoc}
     */
    protected string $view = 'adminhub::tables.columns.thumbnail';

    protected function getStateFromRecord()
    {
        $record = $this->getRecord();

        $name = $this->getName();

        if ($thumbnail = $record->{$name}) {
            return $thumbnail->getUrl('small');
        }

        $variant = $record->variants->first(function ($variant) {
            return $variant->thumbnail;
        });

        return $variant?->thumbnail?->getUrl('small');
    }
}
