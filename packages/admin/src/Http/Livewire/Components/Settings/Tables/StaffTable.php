<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Tables;

use Illuminate\Support\Collection;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Models\SavedSearch;
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

    /**
     * {@inheritDoc}
     */
    public bool $searchable = true;

    /**
     * {@inheritDoc}
     */
    public bool $canSaveSearches = true;

    /**
     * {@inheritDoc}
     */
    protected $listeners = [
        'saveSearch' => 'handleSaveSearch',
    ];

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
     * Remove a saved search record.
     *
     * @param  int  $id
     * @return void
     */
    public function deleteSavedSearch($id)
    {
        SavedSearch::destroy($id);

        $this->resetSavedSearch();

        $this->notify(
            __('adminhub::notifications.saved_searches.deleted')
        );
    }

    /**
     * Save a search.
     *
     * @return void
     */
    public function saveSearch()
    {
        $this->validateOnly('savedSearchName', [
            'savedSearchName' => 'required',
        ]);

        auth()->getUser()->savedSearches()->create([
            'name' => $this->savedSearchName,
            'term' => $this->query,
            'component' => $this->getName(),
            'filters' => $this->filters,
        ]);

        $this->notify(
            __('adminhub::notifications.saved_searches.saved')
        );

        $this->savedSearchName = null;

        $this->emit('savedSearch');
    }

    /**
     * Return the saved searches available to the table.
     *
     * @return Collection
     */
    public function getSavedSearchesProperty(): Collection
    {
        return auth()->getUser()->savedSearches()->whereComponent(
            $this->getName()
        )->get()->map(function ($savedSearch) {
            return [
                'key' => $savedSearch->id,
                'label' => $savedSearch->name,
                'filters' => $savedSearch->filters,
                'query' => $savedSearch->term,
            ];
        });
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
