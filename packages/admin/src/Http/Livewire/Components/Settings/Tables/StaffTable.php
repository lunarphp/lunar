<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Tables;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Hub\Models\Staff;
use GetCandy\Hub\Tables\GetCandyTable;
use GetCandy\LivewireTables\Components\Columns\AvatarColumn;
use GetCandy\LivewireTables\Components\Columns\StatusColumn;
use GetCandy\LivewireTables\Components\Columns\TextColumn;

class StaffTable extends GetCandyTable
{
    use Notifies;

    /**
     * {@inheritDoc}
     */
    public bool $filterable = false;

    public bool $searchable = true;

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        $this->tableBuilder->baseColumns([
            AvatarColumn::make('avatar', function ($record) {
                return $record->email;
            })->gravatar()->heading(false),
            StatusColumn::make('active', function ($record) {
                return ! $record->deleted_at;
            }),
            TextColumn::make('name', function ($record) {
                return $record->fullName;
            })->url(function ($record) {
                return route('hub.staff.show', $record->id);
            }),
            TextColumn::make('email'),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        $query = Staff::query();

        if ($this->query) {
            $query->search($this->query, true);
        }

        return $query->withTrashed()->paginate($this->perPage);
    }
}
