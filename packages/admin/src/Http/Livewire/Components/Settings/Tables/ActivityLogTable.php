<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Tables;

use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Tables\LunarTable;
use Lunar\LivewireTables\Components\Columns\TextColumn;
use Spatie\Activitylog\Models\Activity;

class ActivityLogTable extends LunarTable
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
        return Activity::whereLogName('lunar')->with('causer')->orderBy('created_at', 'desc')->paginate($this->perPage);
    }
}
