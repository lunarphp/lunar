<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Tables;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Hub\Tables\GetCandyTable;
use GetCandy\LivewireTables\Components\Columns\TextColumn;
use Spatie\Activitylog\Models\Activity;

class ActivityLogTable extends GetCandyTable
{
    use Notifies;

    /**
     * {@inheritDoc}
     */
    public bool $filterable = false;

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        $this->tableBuilder->baseColumns([
            TextColumn::make('event'),
            TextColumn::make('subject_id'),
            TextColumn::make('subject_type'),
            TextColumn::make('causer.email'),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        return Activity::whereLogName('getcandy')->with('causer')->orderBy('created_at', 'desc')->paginate($this->perPage);
    }
}
