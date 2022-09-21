<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Tables;

use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Models\Staff;
use Lunar\Hub\Tables\LunarTable;
use Lunar\LivewireTables\Components\Columns\AvatarColumn;
use Lunar\LivewireTables\Components\Columns\StatusColumn;
use Lunar\LivewireTables\Components\Columns\TextColumn;

class StaffTable extends LunarTable
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
